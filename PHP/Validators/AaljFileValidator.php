<?php
namespace Isaac\Validators;

/**
 * Description of AALJFileValidator
 */
class AaljFileValidator extends FileValidator
{    
    public function __construct(array $filteredInputArray)
    {
        $phpFVA = [];

        $phpFEMA = [
            'error'    => 'Bad upload attempt.',
            'size'     => 'Improper file size.',
            'type'     => 'Invalid file type.',
            'name'     => 'Bad file name.',
            'tmp_name' => 'Processing error.'
        ];

        $fileProps = [
            'coverLetter' => ['minSize' => 1, 'maxSize' =>  2097152, 'mimeTypes' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']],
            'manuscript'  => ['minSize' => 1, 'maxSize' => 10485760, 'mimeTypes' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']]
        ];
        
        $validationMA = [
            'coverLetter' => [
                'name'     => ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 128, 'pattern' => '/(?>\A[0-9A-Za-z_-]{1,123}?(?>\.doc|.docx){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
                'type'     => ['kind' => 'mimeType', 'type' => 'string', 'min' => 18, 'max' => 18, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => $this->fileProps['coverLetter']['mimeTypes']],
                'size'     => ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['coverLetter']['minSize'], 'max' => $this->fileProps['coverLetter']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
                'tmp_name' => ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 128, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,128}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
                'error'    => ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => null]
            ],
            'manuscript' => [
                'name'     => ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z-_]{1,251}?(?>\.doc|.docx){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
                'type'     => ['kind' => 'mimeType', 'type' => 'string', 'min' => 18, 'max' => 18, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => $this->fileProps['manuscript']['mimeTypes']],
                'size'     => ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['manuscript']['minSize'], 'max' => $this->fileProps['manuscript']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
                'tmp_name' => ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 128, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,128}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
                'error'    => ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => null]
            ]
        ];

        $validatorTargets = null;
        
        
        $this->setPHPFieldValidationArray($filteredInputArray, $fileProps, $phpFVA);
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInputArray, $validatorTargets, $fileProps);
    }
}
