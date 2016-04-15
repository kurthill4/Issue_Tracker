<?php
/**
 * @file
 * Contains \Drupal\Issue_Tracker\Form\IssueForm.
 */

namespace Drupal\issue_tracker\Form;

// use Drupal\issue_tracker\Controller;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Html;


/**
 * Contribute form.
 */
class IssueForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
	  return 'Issue_Tracker_Form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) 
  { 
	$referer = \Drupal::request()->server->get('HTTP_REFERER');
	$qs = \Drupal::request()->query->get('url');
	//$referer = ('' == $referer) : $qs ? $referer;
	/**
	 * This was WAY harder than it should have been.  Is this the best way (really!?) to tell if this
	 * is the second call through formBuild (after submitting)?  Why doesn't $form_state::IsSubmitted work?
	 *  Anyway, this retains the initial value of the hidden referrer field.
	 */
	if( $_POST['op'] == 'Submit' )
	{
		// Hidden fields:
		$form['hidden_Referer'] = array('#type' => 'hidden', '#value' => $_POST['hidden_Referer'] );
	} 
	else
	{
		// Hidden fields:
		$form['hidden_Referer'] = array('#type' => 'hidden', '#value' => $referer );
	}

	// User (not hidden) fields
	$form['SourceURL'] = array('#type' => 'textfield', '#title' => 'URL', '#required' => TRUE, '#default_value' => $qs );
	$form['email'] = array('#type' => 'email', '#title' => 'Your email address', '#required' => FALSE );
	$form['issue'] = array('#type' => 'textarea', '#title' => 'Problem Description', '#required' => TRUE );
	

	$form['Submit'] = array('#type' => 'submit', '#value' => 'Submit');
	$form['Cancel'] = array('#type' => 'submit', '#value' => 'Cancel', '#submit' => ['::cancelForm']);
	// Debug/testing
	//$form['email']['#default_value'] = 'khill@sdccd.edu';
	//$form['issue']['#default_value'] = 'The web page is not webby enough.  Add more webby stuff.';
	
	return $form;
	  
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) 
  {
	# dpm($form_dtate);
	$url = $form_state->getValue('SourceURL');
	$email = $form_state->getValue('email');
	#Not working... ?
	if(!UrlHelper::isValid($url))
	{
		$form_state->setErrorByName('SourceURL', $this->t('Source URL is invalid!'));
	}
	elseif(!valid_email_address($email))
	{
		$form_state->setErrorByName('email', $this->t('Bad or unrecognized email address!'));
	}

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
	\Drupal\Core\Cache\Cache::invalidateTags(['issuelist']);
	
	$source = $form_state->getValue('SourceURL');
	$referer = $form_state->getValue('hidden_Referer');
	$date = Html::escape($form_state->getValue('ReportDate'));
	$email = Html::escape($form_state->getValue('email'));
	$issue = check_markup($form_state->getValue('issue'));

	/**
	 * Create an issue array that matches the backend-database fields (e.g., fieldname => value)
	 */
	$issue = array (
				'issueID' 		=> NULL,
				'url' 			=> $source,
				'http_referer'	=> $referer,
				'reportDate' 	=> date("Y-m-d H:i:s"  ),
				'email'			=> $email,
				'issue'			=> $issue,
				'status'		=> 0			// 0 = New report
	);
	
	$newIssueID = \Drupal\issue_tracker\Controller\IssueStorage::add($issue);
	$redirect = \Drupal\Core\Url::fromRoute('issue_tracker.thankyou');
	$redirect->setRouteParameters(array('issueid' => $newIssueID));
	$redirect->setRouteParameters(array('i' => $newIssueID));
	$form_state->setRedirectUrl($redirect); // array('issueid' => $newIssueID)
	
	
  }

  public function cancelForm()
  {
	drupal_set_message('Issue report cancelled.');
	  
  }

	public function thankYou($issueid)
	{
		$content = array();
		$content[] = ['#markup' => 'Thank you, your issue has been submitted.  You may close this page.'];

		return $content;
	}  

	
}



