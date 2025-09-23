<?php

namespace App\Service;
use App\Service\ValidationResult; 

class WalkValidationService {

    public function validateWalkData(array $data): ValidationResult {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['description'])) {
            $errors[] = 'Description is required';
        }
        
        if (empty($data['datetime'])) {
            $errors[] = 'Datetime is required';
        }
        
        if (empty($data['photo'])) {
            $errors[] = 'Photo is required';
        }
        
        if (empty($data['location'])) {
            $errors[] = 'Location is required';
        }
        
        if (!isset($data['is_custom_location']) || !is_bool($data['is_custom_location'])) {
            $errors[] = 'is_custom_location must be a boolean';
        }
        
        if (!isset($data['max_participants']) || !is_numeric($data['max_participants'])) {
            $errors[] = 'max_participants must be a number';
        }
        
        return new ValidationResult(empty($errors), $errors);
    }
}

