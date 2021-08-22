<?php

namespace frontend\models;

use yii\base\Model;

class Upload extends Model {
    
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls, xlsx, csv']
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'Upload File'
        ];
    }

    public function validateTemplateFormat($data, $assessment_type)
    {
        if ($assessment_type == 0) {
            $paKeys = ["Group Name", "Matriculation Number", "First Name", "Last Name", "Email"];
        } else if ($assessment_type == 1) {
            $paKeys = ["Matriculation Number", "First Name", "Last Name", "Email", "Work File", "Matriculation Number(Marker Student)", "First Name(Marker Student)", "Last Name(Marker Student)", "Email(Marker Student)"];
        } else {
            return false;
        }
        
        $dataKeys = array_keys($data[0]);

        return (
            is_array($paKeys) 
            && is_array($dataKeys) 
            && count($paKeys) == count($dataKeys) 
            && array_diff($paKeys, $dataKeys) === array_diff($dataKeys, $paKeys)
        );
    }

    public function validateInputContents($data, $assessment_type)
    {
        $valid = true;

        if ($assessment_type == 0) {
            foreach($data as $input){
                $inputValue = array_values($input);
    
                // Empty row, skip
                if(empty($inputValue[0])
                    && empty($inputValue[1])
                    && empty($inputValue[2])
                    && empty($inputValue[3])
                    && empty($inputValue[4])) {

                    continue;
                }
                // Partially input, error   
                else if (empty($inputValue[0])
                    || empty($inputValue[1])
                    || empty($inputValue[2])
                    || empty($inputValue[3])
                    || empty($inputValue[4])) {

                    $valid = false;
                    break;
                }
            }
        } else if ($assessment_type == 1) {
            foreach($data as $input){
                $inputValue = array_values($input);
    
                // Empty row, skip
                if(empty($inputValue[0])
                    && empty($inputValue[1])
                    && empty($inputValue[2])
                    && empty($inputValue[3])
                    && empty($inputValue[4])
                    && empty($inputValue[5])
                    && empty($inputValue[6])
                    && empty($inputValue[7])
                    && empty($inputValue[8])) {
                    
                    continue;
                }
                // Partially input, error   
                else if (empty($inputValue[0])
                    || empty($inputValue[1])
                    || empty($inputValue[2])
                    || empty($inputValue[3])
                    || empty($inputValue[4])
                    || empty($inputValue[5])
                    || empty($inputValue[6])
                    || empty($inputValue[7])
                    || empty($inputValue[8])) {

                    $valid = false;
                    break;
                }
            }
        } else {
            return false;
        }

        return $valid;
    }

    public function validateGroupTemplateFormat($data)
    {
        $paKeys = ["Group Name", "Matriculation Number", "First Name", "Last Name", "Email"];

        $dataKeys = array_keys($data[0]);

        return (
            is_array($paKeys) 
            && is_array($dataKeys) 
            && count($paKeys) == count($dataKeys) 
            && array_diff($paKeys, $dataKeys) === array_diff($dataKeys, $paKeys)
        );
    }

    public function validateInputsContents($data)
    {
        $valid = true;

        foreach($data as $input){
            $inputValue = array_values($input);

            if(empty($inputValue[0]) && empty($inputValue[1]) && empty($inputValue[2]) && empty($inputValue[3]) && empty($inputValue[4])) {
                continue;
            } else if (empty($inputValue[0]) || empty($inputValue[1]) || empty($inputValue[2]) || empty($inputValue[3]) || empty($inputValue[4])) {
                $valid = false;
                break;
            }
        }
        // die();

        return $valid;
    }
}