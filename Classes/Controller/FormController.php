<?php

namespace Rfuehricht\Formhandler\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Rfuehricht\Formhandler\Component\AbstractComponent;
use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use Rfuehricht\Formhandler\Validator\AbstractValidator;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class FormController extends ActionController
{

    protected array $gp = [];


    public function __construct(
        protected readonly FormUtility $formUtility,
        protected readonly Globals     $globals
    )
    {

    }

    public function formAction(): ResponseInterface
    {

        $this->checkPredefinedFormToUse();

        GeneralUtility::makeInstance(AssetCollector::class)
            ->addJavaScript(
                'formhandler',
                'EXT:formhandler/Resources/Public/JavaScript/init.js'
            );

        $originalSettings = $this->settings;
        $this->gp = $this->request->getParsedBody()['tx_formhandler_form'] ?? [];
        if (isset($this->settings['formValuesPrefix'])) {
            $this->gp = $this->request->getParsedBody()['tx_formhandler_form'][$this->settings['formValuesPrefix']] ?? [];
        }
        $this->globals->setRandomId($this->gp['randomId'] ?? $this->formUtility->generateRandomId());
        $this->globals->setView($this->view);
        $this->globals->setSettings($this->settings);
        $this->globals->setFormValuesPrefix($this->settings['formValuesPrefix'] ?? '');

        $wantedStepOrAction = $this->getWantedStepOrAction();
        $currentStep = $this->globals->getSession()->get('currentStep') ?? 1;
        $lastStep = $this->globals->getSession()->get('lastStep') ?? 0;

        if ($this->globals->getSession()->get('finished') || intval($wantedStepOrAction) < $currentStep - 1 || intval($wantedStepOrAction) > $currentStep + 1) {
            $this->gp = [];
            $this->globals->getSession()->reset();
            $currentStep = $lastStep = 1;
        }

        if (empty($this->gp)) {
            $result = $this->runClasses($this->settings['preProcessors'] ?? []);
            if ($result) {
                return $result;
            }
        }
        $this->mergeGPWithSession();
        $this->globals->setValues($this->gp);

        $this->updateSettings($currentStep);

        $result = $this->runClasses($this->settings['interceptors'] ?? []);
        if ($result) {
            return $result;
        }


        $errors = [];

        switch ($wantedStepOrAction) {
            case '':
                $currentStep = 1;
                $lastStep = 1;
                break;
            default:
                $wantedStep = intval($wantedStepOrAction);
                if ($wantedStep === $currentStep + 1) {

                    //Run validations
                    $errors = $this->runValidations();

                    if (empty($errors)) {
                        $currentStep = $wantedStep;
                        $this->storeGPinSession();

                        $this->processFiles();
                    }
                } elseif ($wantedStep === $currentStep || $wantedStep === $currentStep - 1) {

                    //Submit reload action for uploading files
                    /*if ($wantedStep === $currentStep) {
                        //Run validations
                        $errors = $this->runFileValidations();

                        if (empty($errors)) {
                            $this->processFiles();
                        }
                    }*/

                    $currentStep = $wantedStep;
                    if ($currentStep < 1) {
                        $currentStep = 1;
                    }
                    $this->settings = $originalSettings;
                    $this->updateSettings($currentStep);
                }
                break;
        }


        $this->mergeGPWithSession();
        $this->globals->setValues($this->gp);


        $templateFile = null;

        if (isset($this->settings[$currentStep]['templateFile'])) {
            $templateFile = $this->settings[$currentStep]['templateFile'];
        }

        $this->globals->getSession()->set('lastStep', $lastStep);
        $this->globals->getSession()->set('currentStep', $currentStep);

        $skipView = $this->settings['skipView'] ?? false;
        if (!$templateFile || $skipView) {

            $result = $this->runClasses($this->settings['finishers'] ?? []);
            if ($result) {
                return $result;
            }
        }

        $this->globals->setErrors($errors);

        $this->view->assignMultiple([
            'formValuesPrefix' => $this->globals->getFormValuesPrefix(),
            'currentStep' => $currentStep,
            'submit' => [
                'previousStep' => 'submit-' . ($currentStep - 1),
                'nextStep' => 'submit-' . ($currentStep + 1),
                'reload' => 'submit-' . $currentStep
            ],
            'randomId' => $this->globals->getRandomId(),
            'values' => $this->gp,
            'files' => $this->globals->getSession()->get('files'),
            'errors' => $errors
        ]);

        $inlineSettings = [];
        if (isset($this->settings['clientSideValidation']) && boolval($this->settings['clientSideValidation']) === true) {
            $inlineSettings['clientSideValidation'] = true;
        }
        if (isset($this->settings['ajaxSubmit']) && boolval($this->settings['ajaxSubmit']) === true) {
            $inlineSettings['ajaxSubmit'] = true;
        }
        $inlineSettings['formValuesPrefix'] = $this->globals->getFormValuesPrefix();

        if (isset($this->settings['validators'])) {
            foreach ($this->settings['validators'] as $validator) {
                if (isset($validator['config']['fieldConf'])) {
                    foreach ($validator['config']['fieldConf'] as $fieldName => $fieldConf) {
                        if (isset($fieldConf['errorCheck'])) {
                            if (!isset($inlineSettings['validations'])) {
                                $inlineSettings['validations'] = [];
                            }
                            if (!isset($inlineSettings['validations'][$fieldName])) {
                                $inlineSettings['validations'][$fieldName] = [];
                            }
                            foreach ($fieldConf['errorCheck'] as $errorCheck) {
                                $errorCheckName = $errorCheck;
                                if (isset($errorCheck['_typoScriptNodeValue'])) {
                                    $errorCheckName = $errorCheck['_typoScriptNodeValue'];
                                    unset($errorCheck['_typoScriptNodeValue']);
                                }
                                $inlineSettings['validations'][$fieldName][] = [
                                    'check' => $errorCheckName,
                                    'options' => is_array($errorCheck) ? $errorCheck : []
                                ];
                            }
                        }


                    }
                }
            }
        }

        if (!empty($inlineSettings)) {

            $this->view->assign('validations', $inlineSettings['validations']);
            $this->globals->setValidations($inlineSettings['validations'] ?? []);

            /** @var ContentObjectRenderer $contentObjectRenderer */
            $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
            $inlineSettings[$contentObjectRenderer->data['uid']] = $inlineSettings;
            /** @var PageRenderer $pageRenderer */
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addInlineSettingArray('formhandler', $inlineSettings);
        }
        if ($skipView) {
            return new NullResponse();
        }
        return $this->htmlResponse($this->view->render($templateFile));
    }

    /**
     * Updates settings and view if "useForm" is set or predefined form is selected in FlexForm
     *
     * @return void
     */
    protected function checkPredefinedFormToUse(): void
    {
        $configuration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'formhandler');

        $useForm = $configuration['useForm'] ?? $this->settings['useForm'] ?? false;
        if ($useForm && isset($configuration['forms'][$useForm])) {

            $this->settings = $configuration['forms'][$useForm]['settings'] ?? [];

            /** @var RenderingContextInterface $renderingContext */
            $renderingContext = $this->view->getRenderingContext();
            $viewSettings = $configuration['forms'][$useForm]['view'] ?? [];
            $templatePaths = $renderingContext->getTemplatePaths();
            if (isset($viewSettings['layoutRootPaths'])) {
                $paths = $templatePaths->getLayoutRootPaths();
                ArrayUtility::mergeRecursiveWithOverrule($paths, $viewSettings['layoutRootPaths']);
                $templatePaths->setLayoutRootPaths($paths);
                unset($paths);
            }
            if (isset($viewSettings['templateRootPaths'])) {
                $paths = $templatePaths->getTemplateRootPaths();
                ArrayUtility::mergeRecursiveWithOverrule($paths, $viewSettings['templateRootPaths']);
                $templatePaths->setTemplateRootPaths($paths);
                unset($paths);
            }
            if (isset($viewSettings['partialRootPaths'])) {
                $paths = $templatePaths->getPartialRootPaths();
                ArrayUtility::mergeRecursiveWithOverrule($paths, $viewSettings['partialRootPaths']);
                $templatePaths->setPartialRootPaths($paths);
                unset($paths);
            }
            $renderingContext->setTemplatePaths($templatePaths);
            $this->view->setRenderingContext($renderingContext);
            $this->view->assign('settings', $this->settings);
        }
    }

    protected function getWantedStepOrAction(): string
    {
        $wantedStepOrAction = '';
        foreach (array_keys($this->gp) as $key) {
            if (str_starts_with($key, 'submit-')) {
                $wantedStepOrAction = str_replace('submit-', '', $key);
            }
        }

        return $wantedStepOrAction;
    }

    protected function runClasses(array $classes): ?ResponseInterface
    {
        try {
            foreach ($classes as $classSettings) {
                $className = $this->formUtility->prepareClassName($classSettings['class'] ?? 'Default');

                if (is_array($classSettings) && strlen($className) > 0) {
                    /** @var AbstractComponent $classObject */
                    $classObject = GeneralUtility::makeInstance($className);
                    $classObject->init($this->gp, $classSettings['config'] ?? [], $this->request);
                    $result = $classObject->process();

                    if (!is_array($result)) {
                        return $result;
                    }
                    $this->gp = $result;
                }
            }
        } catch (Exception $e) {
            return new HtmlResponse($e->getMessage());
        }
        return null;
    }

    /**
     * Merges the current GET/POST parameters with the stored ones in SESSION
     *
     * @return void
     */
    protected function mergeGPWithSession(): void
    {
        $values = $this->globals->getSession()->get('values') ?? [];

        $valuesToMerge = [];
        foreach ($this->gp as $name => $value) {
            if (!str_starts_with($name, 'submit-') && $name !== 'randomId') {
                $valuesToMerge[$name] = $value;
            }
        }
        ArrayUtility::mergeRecursiveWithOverrule($values, $valuesToMerge);

        $this->gp = $values;
    }

    protected function updateSettings(int $currentStep): void
    {
        if (isset($this->settings[$currentStep])) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $this->settings, $this->settings[$currentStep] ?? []
            );
            $this->globals->setSettings($this->settings);
        }
    }

    protected function runValidations(): array
    {
        $isValid = true;

        $errors = [];
        if (isset($this->settings['validators']) &&
            is_array($this->settings['validators'])) {

            foreach ($this->settings['validators'] as $tsConfig) {
                if ($isValid) {
                    $className = $this->formUtility->prepareClassName($tsConfig['class'] ?? 'Default');

                    if (is_array($tsConfig) && strlen($className) > 0) {
                        /** @var AbstractValidator $validator */
                        $validator = GeneralUtility::makeInstance($className);
                        $validator->init($this->gp, $tsConfig['config'] ?? [], $this->request);
                        $isValid = $validator->validate($errors);

                    }
                }
            }

        }
        return $errors;
    }

    /**
     * Stores the current GET/POST parameters in SESSION
     *
     * @return void
     */
    protected function storeGPinSession(): void
    {
        $data = $this->globals->getSession()->get('values');

        $internalKeys = ['randomId', 'prev', 'next', 'submit'];
        foreach ($this->gp as $key => $value) {
            if (!in_array($key, $internalKeys)) {
                $data[$key] = $this->gp[$key];
            }
        }
        $this->globals->getSession()->set('values', $data);
    }

    /**
     * Processes uploaded files, moves them to a temporary upload folder, renames them if they already exist and
     * stores the information in user session
     *
     * @return void
     */
    protected function processFiles(): void
    {
        $sessionFiles = $this->globals->getSession()->get('files');
        $tempFiles = $sessionFiles;

        $files = $this->formUtility->getFilesArray();

        if (!empty($files)) {
            $uploadedFilesWithSameNameAction = $this->settings['files']['uploadedFilesWithSameName'] ?? 'ignore';

            //if a file was uploaded
            if (isset($files['name']) && is_array($files['name'])) {

                //for all file names
                foreach ($files['name'] as $field => $uploadedFiles) {


                    //If only a single file is uploaded
                    if (!is_array($uploadedFiles)) {
                        $uploadedFiles = [$uploadedFiles];
                    }


                    $uploadPath = $this->formUtility->getUploadFolder($field);

                    foreach ($uploadedFiles as $idx => $name) {
                        $exists = false;
                        if (is_array($sessionFiles[$field])) {
                            foreach ($sessionFiles[$field] as $fileOptions) {
                                if ($fileOptions['name'] === $name) {
                                    $exists = true;
                                }
                            }
                        }

                        if (!$exists || $uploadedFilesWithSameNameAction === 'replace' || $uploadedFilesWithSameNameAction === 'append') {
                            $name = $this->formUtility->doFileNameReplace($name);
                            $filename = substr($name, 0, strpos($name, '.'));
                            if (strlen($filename) > 0) {
                                $ext = substr($name, strpos($name, '.'));
                                $suffix = 1;

                                //build file name
                                $uploadedFileName = $filename . $ext;

                                if ($uploadedFilesWithSameNameAction !== 'replace') {
                                    //rename if exists
                                    while (file_exists($uploadPath . $uploadedFileName)) {
                                        $uploadedFileName = $filename . '_' . $suffix . $ext;
                                        $suffix++;
                                    }
                                }
                                $files['name'][$field][$idx] = $uploadedFileName;

                                //move from temp folder to temp upload folder
                                if (!is_array($files['tmp_name'][$field])) {
                                    $files['tmp_name'][$field] = [$files['tmp_name'][$field]];
                                }
                                move_uploaded_file($files['tmp_name'][$field][$idx], $uploadPath . $uploadedFileName);
                                GeneralUtility::fixPermissions($uploadPath . $uploadedFileName);
                                $files['uploaded_name'][$field][$idx] = $uploadedFileName;

                                //set values for session
                                $tmp['name'] = $name;
                                $tmp['uploaded_name'] = $uploadedFileName;
                                $tmp['uploaded_path'] = $uploadPath;
                                $uploadFolder = str_replace(Environment::getPublicPath(), '', $uploadPath);
                                $tmp['uploaded_folder'] = $uploadFolder;

                                $uploadedUrl = rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/');
                                $uploadedUrl .= '/' . trim($uploadFolder, '/') . '/';
                                $uploadedUrl .= trim($uploadedFileName, '/');

                                $tmp['uploaded_url'] = $uploadedUrl;
                                if (is_array($files['size'][$field][$idx])) {
                                    $tmp['size'] = $files['size'][$field][$idx];
                                } else {
                                    $tmp['size'] = $files['size'][$field];
                                }
                                if (is_array($files['type'][$field][$idx])) {
                                    $tmp['type'] = $files['type'][$field][$idx];
                                } else {
                                    $tmp['type'] = $files['type'][$field];
                                }
                                if (!is_array($tempFiles[$field]) && strlen($field) > 0) {
                                    $tempFiles[$field] = [];
                                }
                                if (!$exists || $uploadedFilesWithSameNameAction !== 'replace') {
                                    $tempFiles[$field][] = $tmp;
                                    foreach ($tempFiles[$field] as $fileIndex => &$tempFile) {
                                        $tempFile['index'] = $fileIndex;
                                        $tempFile['field'] = $field;
                                    }
                                }
                                if (!is_array($this->gp[$field])) {
                                    $this->gp[$field] = [];
                                }
                                if (!$exists || $uploadedFilesWithSameNameAction !== 'replace') {
                                    $this->gp[$field][] = $uploadedFileName;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->globals->getSession()->set('files', $tempFiles);
    }

}
