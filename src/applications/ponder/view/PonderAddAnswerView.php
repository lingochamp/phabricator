<?php

final class PonderAddAnswerView extends AphrontView {

  private $question;
  private $actionURI;
  private $draft;

  public function setQuestion($question) {
    $this->question = $question;
    return $this;
  }

  public function setActionURI($uri) {
    $this->actionURI = $uri;
    return $this;
  }

  public function render() {
    $question = $this->question;
    $viewer = $this->user;

    $authors = mpull($question->getAnswers(), null, 'getAuthorPHID');
    if (isset($authors[$viewer->getPHID()])) {
      return id(new PHUIInfoView())
        ->setSeverity(PHUIInfoView::SEVERITY_NOTICE)
        ->setTitle(pht('Already Answered'))
        ->appendChild(
          pht(
            'You have already answered this question. You can not answer '.
            'twice, but you can edit your existing answer.'));
    }

    $info_panel = null;
    if ($question->getStatus() != PonderQuestionStatus::STATUS_OPEN) {
      $info_panel = id(new PHUIInfoView())
        ->setSeverity(PHUIInfoView::SEVERITY_NOTICE)
        ->appendChild(
          pht(
            'This question has been marked as closed,
             but you can still leave a new answer.'));
    }

    $box_style = null;
    $header = id(new PHUIHeaderView())
      ->setHeader(pht('New Answer'))
      ->addClass('ponder-add-answer-header');

    $form = new AphrontFormView();
    $form
      ->setUser($this->user)
      ->setAction($this->actionURI)
      ->setWorkflow(true)
      ->setFullWidth(true)
      ->addHiddenInput('question_id', $question->getID())
      ->appendChild(
        id(new PhabricatorRemarkupControl())
          ->setName('answer')
          ->setLabel(pht('Answer'))
          ->setError(true)
          ->setID('answer-content')
          ->setUser($this->user))
      ->appendChild(
        id(new AphrontFormSubmitControl())
          ->setValue(pht('Add Answer')));

    if (!$viewer->isLoggedIn()) {
      $login_href = id(new PhutilURI('/auth/start/'))
          ->setQueryParam('next', '/Q'.$question->getID());
      $form = id(new PHUIFormLayoutView())
        ->addClass('login-to-participate')
        ->appendChild(
          id(new PHUIButtonView())
          ->setTag('a')
          ->setText(pht('Login to Answer'))
          ->setHref((string)$login_href));
    }

    $box = id(new PHUIObjectBoxView())
      ->appendChild($form)
      ->setBackground(PHUIObjectBoxView::GREY)
      ->addClass('ponder-add-answer-view');

    if ($info_panel) {
      $box->setInfoView($info_panel);
    }

    return array($header, $box);
  }
}
