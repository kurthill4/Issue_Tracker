<?php
/**
 * TODO:  Break out the storage class into it's own file.
 */

namespace Drupal\issue_tracker\Controller;

class IssueStorage
{

	/**
	 * Add new issue.
	 * Returns issue ID
	 */
	static function add( &$issue)
	{
		unset($issue['issueID']);
		$cn = \Drupal::database();
		
		$obj = $cn->insert('issue_tracker');
		$obj->fields($issue);
		$newID = $obj->execute();
		//$issue['issueID'] = 20;
		$issue = ['issueID' => $newID] + $issue;
		
		return $issue['issueID'];
		
	}

	/**
	 * Add new issue.
	 * Returns issue ID
	 */
	static function closeIssue($issueid)
	{
		$query = \Drupal::database()->update('issue_tracker');
		$query->condition('issueid', $issueid);
		$query->fields( ['status' => -1] );
		$query->execute();
		
	}


	/**
	 * Return an issue as an issue array.
	 */
	static function get( $issueID )
	{
				
		$cn = \Drupal::database();
		$si = $cn->select('issue_tracker', 'i');	// Returns a SelectInterface
		$si->fields('i');							// Uses '*' if no field array passed.  Returns SelectInterface.
		$si->condition('i.issueID', $issueID);
		
		// This returns an associative array (fieldname->value) or NULL if no match.
		$result = $si->execute()->fetchObject();

		return $result;	
	
	}
	
	/**
	 * Return a "prepared statement" for use with a foreach...
	 */
	static function getArray($whereSQL = NULL, $args = array() )
	{
		$cn = \Drupal::database();
		$si = $cn->select('issue_tracker', 'i');	// Returns a SelectInterface
		$si->fields('i');							// Uses '*' if no field array passed.  Returns SelectInterface.
		
		if( $whereSQL != NULL )
		{
			$si->where($whereSQL, $args);
		}
		$st=$si->execute();							// Returns a "prepared statement"...
		return $st;
	}

	/**
	 * Get a "prepared statement" for a paged result set.
	 **/
	static function getPaged($whereSQL = NULL, $args = array() )
	{
		$cn = \Drupal::database();
		$query = $cn->select('issue_tracker', 'i');
		$query->fields('i');							// Uses '*' if no field array passed.  Returns SelectInterface.
		
		$paged_query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
		$paged_query->limit(20);
		
		return $paged_query->execute();
		
	}

	/**
	 * 
	 **/
	static function getPagedQueryInterface()
	{
		$cn = \Drupal::database();
		$query = $cn->select('issue_tracker', 'i');
		
		$paged_query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');
		//$paged_query = $query->extend('Drupal\Core\Database\Query\TableSortExtender');
		
		$paged_query->fields('i');	// Uses '*' if no field array passed.  Returns SelectInterface.
		$paged_query->limit(5);
		
		return $paged_query;
		
	}


	/**
	 * Get a "prepared statement" for a paged result set for notes for a given issue ID.
	 **/
	static function getNotes($issueid)
	{
		$cn = \Drupal::database();
		$query = $cn->select('issue_tracker_notes', 'n');
		$query->fields('n');
		$query->condition('n.issueid', $issueid);	// Uses '*' if no field array passed.  Returns SelectInterface.
		
		return $query->execute();
		
	}
	
	
}

