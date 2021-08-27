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
                        $groupDetailStudent->addError('contribution', 'Contributions for each item should be add to 100.');

                    }
                }
            }
        }

        return $valid;
    }
}
