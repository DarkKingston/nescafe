<?php

namespace Drupal\ln_quiz;

interface LnQuizConstants {
  const FIELD_QUIZ_QUESTIONS = 'field_quiz_questions';
  const FIELD_QUIZ_ANSWER = 'field_quiz_answers';
  const FIELD_QUIZ_ANSWER_BOOL = 'right';
  const QUESTION_CORRECT_VIEW_MODE = 'quiz_question_correct';
  const QUESTION_WRONG_VIEW_MODE = 'quiz_question_wrong';
  const QUIZ_SUMMARY_VIEW_MODE = 'quiz_summary';

  const QUIZ_ACTION_INIT = 'init';
  const QUIZ_ACTION_START = 'start';
  const QUIZ_ACTION_QUESTION = 'question';
  const QUIZ_ACTION_CHECK = 'check';
  const QUIZ_ACTION_SUMMARY = 'summary';
  const QUIZ_ACTION_RESET = 'reset';
}
