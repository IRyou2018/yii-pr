<?php

namespace frontend\models;

use yii\base\Model;

/**
 * Validate summary of contributions.
 */
class ArrayValidator extends Model
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            
        ];
    }

    public function validateCreateAssessment($modelsSection, $modelsItem) {

        $valid = true;

        $totalMark = 0;

        foreach ($modelsItem as $indexSection => $items) {
            foreach ($items as $item) {

                $totalMark += $item->max_mark_value;

                if ($modelsSection[$indexSection]->section_type == 0
                    && $item->item_type <> 0) {
                    $valid = false;
                    $item->addError('item_type', 'Only Individule Item could be select for the section type of "For Student".');
                }
            }
        }

        if ($totalMark <> 100) {
            $valid = false;
            foreach ($modelsItem as $items) {
                foreach ($items as $item) {
                    $item->addError('max_mark_value', 'Total max marks should be 100.');
                }
            }
        }

        return $valid;
    }

    public function validateGroupDetail($attribute, $count) {
        
        $valid = true;

        foreach ($attribute as $groupDetailsSection) {
            foreach ($groupDetailsSection as $groupDetailsItem) {

                $summary = 0;
            
                foreach ($groupDetailsItem as $groupDetail) {

                    $contribution = $groupDetail->contribution;
                    $summary += $contribution;

                    if(((100/$count+10)<=$contribution || (100/$count-10)>=$contribution)
                        && empty($groupDetail->comment)) {

                        $valid = false;
                        $groupDetail->addError('comment', 'Comments need to be added because of the difference of contribution allocation.');
                    }
                }

                if ($summary <> 100) {

                    $valid = false;
                    foreach ($groupDetailsItem as $groupDetailStudent) {
                        $groupDetailStudent->addError('contribution', 'Summary of contributions should be 100.');

                    }
                }
            }
        }

        return $valid;
    }

    public function validateInputMarks($modelsIndividualFeedback, $proposedMarkList, $markerCommentsList) {

        $valid = true;

        foreach ($modelsIndividualFeedback as $indexSection => $individualFeedbacks) {
            foreach ($individualFeedbacks as $indexItem => $individualFeedback) {               

                if($individualFeedback->item->section->section_type == 0) {

                    if (empty($individualFeedback->mark) && empty($proposedMarkList[$indexSection][$indexItem])) {
                    
                        $individualFeedback->addError('mark', 'Supposed Mark is empty. Please enter a mark.');
                        $valid = false;
                    }

                    if (empty($individualFeedback->comment) && empty($markerCommentsList[$indexSection][$indexItem])) {
                        $individualFeedback->addError('comment', 'Please enter your comment.');
                        $valid = false;
                    }
                } else {
                    if (empty($individualFeedback->mark)) {
                    
                        $individualFeedback->addError('mark', 'Please enter a mark.');
                        $valid = false;
                    }

                    if (empty($individualFeedback->comment)) {
                        $individualFeedback->addError('comment', 'Please enter your comment.');
                        $valid = false;
                    }
                }
            }
        }

        return $valid;
    }

    public function validateGroupComments($modelsGroupAssessmentFeedback, $markerCommentsList) {

        $valid = true;

        foreach ($modelsGroupAssessmentFeedback as $indexSection => $groupIndividualFeedbacks) {
            foreach ($groupIndividualFeedbacks as $indexItem => $feedbacks) {               
                foreach ($feedbacks as $indexStudent => $feedback) {
                    if($feedback->item->section->section_type == 0) {
                        if (empty($feedback->comment) && empty($markerCommentsList[$indexSection][$indexItem][$indexStudent])) {
                            $feedback->addError('comment', 'Please enter your comment.');
                            $valid = false;
                        }
                    } else {

                        if (empty($feedback->comment)) {
                            $feedback->addError('comment', 'Please enter your comment.');
                            $valid = false;
                        }
                    }
                }
            }
        }

        return $valid;
    }
}
