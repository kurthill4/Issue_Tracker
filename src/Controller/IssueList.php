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
		$header = $this->getTableHeader();
		
		$paged_query = \Drupal\issue_tracker\Controller\IssueStorage::getPagedQueryInterface();
		//$paged_query = $paged_query->orderbyHeader($header);
		$result = $paged_query->extend('Drupal\Core\Database\Query\TableSortExtender')->orderbyHeader($header)->execute();
		
		$items = array();
		
		// $items['#sticky'] = 'TRUE';
		foreach($result as $row)
		{
			$issueid = str_pad($row->issueID, 5, '0', STR_PAD_LEFT);
			$source = $row->url;
			if( $row->http_referer != $row->url)
			{
				$source .= '<br />' . $row->http_referer;
				$source = \Drupal\Core\Render\Markup::create($source);
			}
			
			$link = Url::fromRoute( 'issue_tracker.edit', array('issueid' => $row->issueID) );
			$link = \Drupal::l($issueid, $link);
						
			$issue=\Drupal\Core\Render\Markup::create($row->issue);
			$items[] = ['data' => [$link, $row->reportDate, $row->email, $issue, $source, $row->status] ];
			//$items[] = ['data' => (array) $row];
		}
		
		$build=array();
		$build['tablesort_table'] = ['#theme' => 'table', '#header' => $header, '#rows' => $items ];
		$build['item_pager'] = ['#type' => 'pager'];
		
		return $build;
	}
	
	private function getTableHeader()
	{
		$header= [
					array('data' => t('Issue ID'), 			'field' => 'i.issueID'),
					array('data' => t('Reported On'), 		'field' => 'i.reportDate'),
					array('data' => t('Reported By'), 		'field' => 'i.email'),
					array('data' => t('Issue Description') ),
					array('data' => t('URL'), 				'field' => 'i.url'),
					array('data' => t('Status'), 			'field' => 'i.status')
				];
		
		return $header;
	}
}

