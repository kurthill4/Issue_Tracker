<?php
/**
 * @file
 * Contains \Drupal\Issue_Tracker\Form\IssueEditForm
 */

namespace Drupal\issue_tracker\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\issue_tracker\Controller;

class IssueEditForm extends FormBase
{
	
	/**
	* {@inheritdoc}
	*/
	public function getFormId() 
	{
		return 'issue_tracker_EditForm';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $issueid = NULL) 
	{

		$output['Issue'] = $this->getIssueRenderArray($issueid);
		$output['IssueHistory'] = $this->getNotesRenderArray($issueid);
		$output['Note'] = ['#type' => 'textarea', '#title' => 'Notes', '#required' => TRUE ];
		dpm($output);
		$output['Submit'] = ['#type' => 'submit', '#value' => 'Submit'];
		$output['Close'] = ['#type' => 'submit', '#value' => 'Close Issue', '#submit' => ['::submitForm_closeIssue']];
		$output['Cancel'] = ['#type' => 'submit', '#value' => 'Cancel', '#submit' => ['::cancelForm']];
		
		$output['hidden_issueid'] = ['#type' => 'hidden', '#value' => $issueid];

		$output[] = ['#cache' => ['tags' => ['issueeditform'], 'max-age' => 0]];		
		dpm($output);
		return $output;
		// Return array of Form API elements.
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form,  FormStateInterface $form_state) 
	{
		// Validation covered in later recipe, required to satisfy interface
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form,  FormStateInterface $form_state) 
	{
		$formdata = $this->parseForm($form_state);
		$this->writeNote($formdata);
		
		\Drupal\Core\Cache\Cache::invalidateTags(['issuelist', 'issueeditform']);
	}
	
	public function submitForm_closeIssue(array &$form,  FormStateInterface $form_state, $issueid = NULL)
	{
		$formdata = $this->parseForm($form_state);
		$this->writeNote($formdata);
		\Drupal\issue_tracker\Controller\IssueStorage::closeIssue($formdata['issueID']);
		
		\Drupal\Core\Cache\Cache::invalidateTags(['issuelist', 'issueeditform']);
		
	}

	private function parseForm(FormStateInterface $form_state)
	{

		$issueid = $form_state->getValue('hidden_issueid');
		$date = date("Y-m-d H:i:s"  );
		$note = $form_state->getValue('Note');
		/**
		 * Create an issue array that matches the backend-database fields (e.g., fieldname => value)
		 */
		$notes = array (
					'issueID' 	=> $issueid,
					'Date' 		=> $date,
					'Note'		=> $note,
		);		
		return $notes;
	}

	private function writeNote($notes)
	{
			$cn = \Drupal::database();
			
			$obj = $cn->insert('issue_tracker_notes');
			$obj->fields($notes);
			$obj->execute();
	}		

	private function getIssueRenderArray($issueid)
	{
		$issue = \Drupal\issue_tracker\Controller\IssueStorage::get($issueid);

		$output = array();
		$output['issue'][] = ['#plain_text' => 'HTTP Referer: ' . $issue->http_referrer, '#suffix' => '<br />'];
		$output['issue'][] = ['#plain_text' => 'Date Reported: ' . $issue->reportDate, '#suffix' => '<br />'];
		$output['issue'][] = ['#markup' => 'Reported by: ' . $issue->email, '#suffix' => '<br />'];
		
		$output['issue'][] = ['#markup' => 'Issue: ' . $issue->issue, '#suffix' => '<hr />'];
		$output['issue'] = ['#cache' => ['max-age' => 0]];
		return $output;
	}
	
	private function getNotesRenderArray($issueid)
	{
		$result = \Drupal\issue_tracker\Controller\IssueStorage::getNotes($issueid);
		
		$items = array();
		$header = ['Date', 'Notes'];
		
		$items['#header'] = $header;
		
		foreach($result as $row)
		{
			$items[] = ['rowdata' => $row->Date, $row->Note];
			drupal_set_message($row->note);
		}

		$build=array();
		$build['items'] = ['#theme' => 'table', '#rows' => $items ];
		
		return $build;
	}
	
	public function getPageTitle($issueid)
	{
		return 'Editing Issue: ' . str_pad($issueid, 5, '0', STR_PAD_LEFT);
	}
}
