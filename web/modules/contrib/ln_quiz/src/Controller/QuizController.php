<?php

namespace Drupal\ln_quiz\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\Url;
use Drupal\ln_quiz\Ajax\QuizRemoveLocalStorage;
use Drupal\ln_quiz\Ajax\QuizSetLocalStorage;
use Drupal\ln_quiz\LnQuizConstants;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class QuizController extends ControllerBase {


  /**
   * @var \Drupal\paragraphs\ParagraphViewBuilder
   */
  protected $paragraphViewBuilder;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->paragraphViewBuilder = $entityTypeManager->getViewBuilder('paragraph');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function process(ParagraphInterface $quiz, $action, Request $request){
    if($request->get('_drupal_ajax')){
      $quiz_storage = Json::decode($request->get('quizStorageJson', '[]'));
      $question_id = $request->get('question');
      $answer = $request->get('answer');
      $reload = FALSE;
      //We take the quiz where we left off
      if(!empty($quiz_storage['workflow']) && $action == LnQuizConstants::QUIZ_ACTION_INIT){
        $action = $quiz_storage['workflow'];
        $reload = TRUE;
        if(!empty($quiz_storage['current_question'])){
          $question_id = $quiz_storage['current_question'];
        }
        if(!empty($quiz_storage['answer'])){
          $answer = $quiz_storage['answer'];
        }
      }

      switch ($action) {
        case LnQuizConstants::QUIZ_ACTION_START:
          return $this->start($quiz, $quiz_storage);
        case LnQuizConstants::QUIZ_ACTION_QUESTION:
          $question = Paragraph::load($question_id);
          return $this->question($quiz, $question, $quiz_storage);
        case LnQuizConstants::QUIZ_ACTION_CHECK:
          $question = Paragraph::load($question_id);
          return $this->check($quiz, $question, $answer, $quiz_storage, $reload);
        case LnQuizConstants::QUIZ_ACTION_SUMMARY:
          return $this->summary($quiz, $quiz_storage);
        case LnQuizConstants::QUIZ_ACTION_RESET:
          return $this->reset($quiz);
      }
    }

    throw new NotFoundHttpException();
  }

  public function start(ParagraphInterface $paragraph, $quiz_storage){
    try {
      /** @var ParagraphInterface $first_question */
      $first_question = $paragraph->get(LnQuizConstants::FIELD_QUIZ_QUESTIONS)->get(0)->entity;

      $quiz_storage['current_question'] = $first_question->id();
      $quiz_storage['workflow'] = LnQuizConstants::QUIZ_ACTION_START;
      $quiz_storage['corrects'] = 0;

      $response = new AjaxResponse();
      $response->addCommand(new QuizSetLocalStorage("Drupal.quiz_{$paragraph->id()}", $quiz_storage));
      $response->addCommand(new HtmlCommand("[data-quiz-id={$paragraph->id()}]",$this->paragraphViewBuilder->view($first_question)));
      return $response;
    } catch (MissingDataException $e) {
      throw BadRequestHttpException;
    }
  }

  public function check(ParagraphInterface $quiz, ParagraphInterface $question, $answer, $quiz_storage, $reload = FALSE){
    $answer_question = $question->get(LnQuizConstants::FIELD_QUIZ_ANSWER)->get($answer)->get(LnQuizConstants::FIELD_QUIZ_ANSWER_BOOL)->getString();

    if( $answer_question ){
      $view_mode = LnQuizConstants::QUESTION_CORRECT_VIEW_MODE;
      if(!$reload){
        $quiz_storage['corrects']++;
      }
    }else{
      $view_mode = LnQuizConstants::QUESTION_WRONG_VIEW_MODE;
    }

    $quiz_storage['workflow'] = LnQuizConstants::QUIZ_ACTION_CHECK;
    $quiz_storage['current_question'] = $question->id();
    $quiz_storage['answer'] = $answer;


    $response = new AjaxResponse();
    $response->addCommand(new QuizSetLocalStorage("Drupal.quiz_{$quiz->id()}", $quiz_storage));
    $response->addCommand(new HtmlCommand("[data-quiz-id={$quiz->id()}]",$this->paragraphViewBuilder->view($question,$view_mode)));
    return $response;
  }

  public function question(ParagraphInterface $quiz, ParagraphInterface $question, $quiz_storage){
    $quiz_storage['workflow'] = LnQuizConstants::QUIZ_ACTION_QUESTION;
    $quiz_storage['current_question'] = $question->id();

    $response = new AjaxResponse();
    $response->addCommand(new QuizSetLocalStorage("Drupal.quiz_{$quiz->id()}", $quiz_storage));
    $response->addCommand(new HtmlCommand("[data-quiz-id={$quiz->id()}]",$this->paragraphViewBuilder->view($question)));
    return $response;
  }

  public function summary(ParagraphInterface $paragraph, $quiz_storage){
    $quiz_storage['workflow'] = LnQuizConstants::QUIZ_ACTION_SUMMARY;

    $response = new AjaxResponse();
    $response->addCommand(new QuizSetLocalStorage("Drupal.quiz_{$paragraph->id()}", $quiz_storage));
    $response->addCommand(new ReplaceCommand("[data-quiz-id={$paragraph->id()}]",$this->paragraphViewBuilder->view($paragraph,LnQuizConstants::QUIZ_SUMMARY_VIEW_MODE)));
    return $response;
  }

  public function reset(ParagraphInterface $paragraph){
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("[data-quiz-id={$paragraph->id()}]",$this->paragraphViewBuilder->view($paragraph)));
    $response->addCommand(new QuizRemoveLocalStorage("Drupal.quiz_{$paragraph->id()}"));
    return $response;
  }
}

