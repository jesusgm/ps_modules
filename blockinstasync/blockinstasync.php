<?php

    if (!defined('_PS_VERSION_'))
        exit;

    class BlockInstaSync extends Module{
        public function __construct(){
            $this->name = 'blockinstasync';
            $this->tab = 'front_office_features';
            $this->version = '1.0.0';
            $this->author = 'Jesús Gándara';
            $this->need_instance = 0;
            $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
            $this->bootstrap = true;

            parent::__construct();

            $this->displayName = $this->l('Instagram Sync');
            $this->description = $this->l('Allows to sync your Instagram pics width your Prestashop products to increase your sells');

            $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

            if (!Configuration::get('BLOCKINSTASYNC_ACCESS_TOKEN'))
                $this->warning = $this->l('No access token provided. You can get it here http://instagram.pixelunion.net/');
        }


        public function install(){
            if (Shop::isFeatureActive())
                Shop::setContext(Shop::CONTEXT_ALL);

            if (!parent::install() || !$this->registerHook('leftColumn') || !$this->registerHook('header') || !Configuration::updateValue('BLOCKINSTASYNC_ACCESS_TOKEN', ''))
                return false;

            return true;
        }

        public function uninstall(){
            if (!parent::uninstall() || !Configuration::deleteByName('BLOCKINSTASYNC_ACCESS_TOKEN'))
                return false;

            return true;
        }

        public function getContent()
        {
            $output = null;

            $output .= $this->getIntagramMedia();

            if (Tools::isSubmit('submit'.$this->name)){
                $access_token = Tools::getValue('BLOCKINSTASYNC_ACCESS_TOKEN');
                $photo_count = Tools::getValue('BLOCKINSTASYNC_PHOTO_COUNT');

                if(!empty($photo_count)){
                    Configuration::updateValue('BLOCKINSTASYNC_PHOTO_COUNT', $photo_count);
                }

                if (!$access_token || empty($access_token)){
                    $output .= $this->displayError($this->l('Invalid Access token'));
                }else{
                    Configuration::updateValue('BLOCKINSTASYNC_ACCESS_TOKEN', $access_token);
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
                }

                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            }
            return $output.$this->displayForm();
        }

        public function displayForm(){
            // Get default language
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            // Init Fields form array
            $fields_form[0]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Your instagram access token'),
                        'description' => $this->l('You can get it here: http://instagram.pixelunion.net/'),
                        'name' => 'BLOCKINSTASYNC_ACCESS_TOKEN',
                        'size' => 20,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of images to obtain'),
                        'name' => 'BLOCKINSTASYNC_PHOTO_COUNT',
                        'size' => 20,
                        'required' => true
                    )
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                )
            );

            $helper = new HelperForm();

            // Module, token and currentIndex
            $helper->module = $this;
            $helper->name_controller = $this->name;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

            // Language
            $helper->default_form_language = $default_lang;
            $helper->allow_employee_form_lang = $default_lang;

            // Title and toolbar
            $helper->title = $this->displayName;
            $helper->show_toolbar = true;        // false -> remove toolbar
            $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
            $helper->submit_action = 'submit'.$this->name;
            $helper->toolbar_btn = array(
                'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
                'back' => array(
                    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                )
            );

            // Load current value
            $helper->fields_value['BLOCKINSTASYNC_ACCESS_TOKEN'] = Configuration::get('BLOCKINSTASYNC_ACCESS_TOKEN');
            $helper->fields_value['BLOCKINSTASYNC_PHOTO_COUNT'] = Configuration::get('BLOCKINSTASYNC_PHOTO_COUNT');

            return $helper->generateForm($fields_form);
        }


        protected function getIntagramMedia(){
            // use this instagram access token generator http://instagram.pixelunion.net/
            $access_token = Configuration::get('BLOCKINSTASYNC_ACCESS_TOKEN');
            $photo_count = Configuration::get('BLOCKINSTASYNC_PHOTO_COUNT', 9);

            $json_link="https://api.instagram.com/v1/users/self/media/recent/?";
            $json_link.="access_token={$access_token}&count={$photo_count}";
            $json = file_get_contents($json_link);
            $media_array = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

            return $this->formatImagesFrom($media_array);
        }

        protected function formatImagesFrom($img_array){
            // echo "<pre>";
            // print_r($img_array);
            // die();
            $toret = '<div class="instagramimages panel">';
            $toret .= '<div class="panel-heading">'.$this->l('List of images of ').$img_array['data'][0]['user']['full_name'].'</div>';
            foreach ($img_array['data'] as $imgdata) {

                $toret .= '<img src="'.$imgdata['images']['thumbnail']['url'].'" />';
            }
            $toret .= '</div>';

            return $toret;
        }

    }


 ?>
