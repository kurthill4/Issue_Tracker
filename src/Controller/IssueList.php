<?php
/**
 * @file
 * Contains \Drupal\Issue_Tracker\Form\IssueForm.
 */

namespace Drupal\issue_tracker\Controller;

use Drupal\Core\Url;


class IssueList
{
	
	public function build()
	{
		$result = \Drupal\issue_tracker\Controller\IssueStorage::getPaged();
		$items = array();
		
		$header = $this->getTableHeader();
		$items['#header'] = $header;
		// $items['#sticky'] = 'TRUE';
		foreach($result as $row)
		{
			$issueid = str_pad($row->issueID, 5, '0', STR_PAD_LEFT);
			$source = $row->url;
			if( $row->http_referer != $row->url)
			{
				$source .= '<br />' . $row->http_referer;
			}
			
			$link = Url::fromRoute( 'issue_tracker.edit', array('issueid' => $row->issueID) );
			$link = \Drupal::l($issueid, $link);
			
			$items[] = ['data' => [$link, $row->reportDate, $row->email, $row->issue, $source, $row->status] ];
		}
		
		$build=array();
		$build['items'] = ['#theme' => 'table', '#rows' => $items ];
		$build['item_pager'] = ['#type' => 'pager'];
		
		return $build;
	}
	
	private function getTableHeader()
	{
		$header= [
					'issueID' => 'Issue ID',
					'reportDate' => 'Reported On',
					'email' => 'Reported By',
					'issue' => 'Issue Description',
					'source' => 'URL',
					'status' => 'Status'
				];
		
		return $header;
	}
}

