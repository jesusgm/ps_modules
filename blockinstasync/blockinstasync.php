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

            //$output .= $this->getIntagramMedia();
            // echo "<pre>";
            // print_r($_POST);
            // die();
            if (Tools::isSubmit('submit'.$this->name)){
                $access_token = Tools::getValue('BLOCKINSTASYNC_ACCESS_TOKEN');

                if (!$access_token || empty($access_token)){
                    $output .= $this->displayError($this->l('Invalid Access token'));
                }else{
                    Configuration::updateValue('BLOCKINSTASYNC_ACCESS_TOKEN', $access_token);
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
                }

                if(Tools::isSubmit('BLOCKINSTASYNC_DOSYNC') && !empty(Tools::getValue('BLOCKINSTASYNC_DOSYNC'))){
                    $this->syncInstagramMedia();
                }

                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            }
            if(Tools::isSubmit('submitblockinstasync_associations')){
                echo "<pre>";
                print_r($_POST);
                die();
                //TODO: Create a table in db to save this data in the install method and load it in backform
            }


            return $output.$this->displayForm();
        }

        public function displayForm(){
            // Get default language
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

            //Init the forms
            $fields_form[] = $this->initTokenForm();
            $fields_form[] = $this->initSyncForm();


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

            return $helper->generateForm($fields_form) . $this->getImagesForm();
        }

        protected function initTokenForm(){
            // Init Fields form array
            $fields_form = array();
            $fields_form['form'] = array(
                'legend' => array(
                    'title' => $this->l('Configurando token instagram'),
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
                ),

                'submit' => array(
                    'title' => $this->l('Guardar'),
                    'class' => 'btn btn-default pull-right'
                )
            );
            return $fields_form;
        }

        protected function initSyncForm(){
            // Init Fields form array
            $fields_form = array();
            $fields_form['form'] = array(
                'legend' => array(
                    'title' => $this->l('Sincronizar imagenes desde instagram'),
                ),
                // 'input' => array(
                //     array(
                //         'type' => 'hidden',
                //         'value' => 1
                //     ),
                // ),

                'submit' => array(
                    'title' => $this->l('Sincronizar'),
                    'name' => 'BLOCKINSTASYNC_DOSYNC',
                    'class' => 'btn btn-default pull-right'
                )
            );

            return $fields_form;
        }

        protected function initRelForm(){
            // Init Fields form array
            $fields_form = array();
            $fields_form['form'] = array(
                'legend' => array(
                    'title' => $this->l('Sincronizar imagenes desde instagram'),
                ),
                // 'input' => array(
                //     array(
                //         'type' => 'hidden',
                //         'value' => 1
                //     ),
                // ),

                'submit' => array(
                    'title' => $this->l('Sincronizar'),
                    'name' => 'BLOCKINSTASYNC_DOSYNC',
                    'class' => 'btn btn-default pull-right'
                )
            );

            return $fields_form;
        }



        protected function syncInstagramMedia(){
            // use this instagram access token generator http://instagram.pixelunion.net/
            $access_token = Configuration::get('BLOCKINSTASYNC_ACCESS_TOKEN');

            $json_link="https://api.instagram.com/v1/users/self/media/recent/?";
            $json_link.="access_token={$access_token}";
            $imagenes = array();
            $imagenes = $this->getimages($json_link);

            foreach($imagenes as $img){

                $id = $img['id'];
                $link = $img['link'];
                $username = $img['user']['full_name'];
                $latitude = $img['location']['latitude'];
                $longitude = $img['location']['longitude'];
                $location_name = $img['location']['name'];

                $insert = 'INSERT INTO `'._DB_PREFIX_.'instagramsync_images`
                            VALUES(
                                0,
                                "' . $id . '",
                                "' . $link . '",
                                "' . $username . '",
                                "' . $latitude . '",
                                "' . $longitude . '",
                                "' . $location_name . '"
                            )';
                Db::getInstance()->execute($insert);

                $img_dir = __DIR__.'/images/'. $id;

                if(!file_exists($img_dir)){
                    mkdir($img_dir, 0755, true);
                }
                //Download standard
                $standard_resolution = file_get_contents($img['images']['standard_resolution']['url']);
                file_put_contents($img_dir.'/standard_resolution.jpg', $standard_resolution);
                //Download low_resolution
                $low_resolution = file_get_contents($img['images']['low_resolution']['url']);
                file_put_contents($img_dir.'/low_resolution.jpg', $low_resolution);
                //Download thumbnail
                $thumbnail = file_get_contents($img['images']['thumbnail']['url']);
                file_put_contents($img_dir.'/thumbnail.jpg', $thumbnail);
            }

        }

        protected function getImages($url){
            $imagenes = array();
            $json = file_get_contents($url);
            $media_array = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
            $next_url = $media_array['pagination']['next_url'];
            foreach ($media_array['data'] as $img) {
                $imagenes[] = $img;
            }
            if(empty($media_array['pagination']['next_url'])){
                return $imagenes;
            }else{
                return array_merge($imagenes, $this->getImages($next_url));
            }

        }

        /*
            This function creates a form that contain a img and allows to asociate it to a pretashop product
            Param: $img_array -> array of images
            Returns: a html of a form width the images to print in a getContent function
        */
        protected function getImagesForm(){
            $sql = "SELECT * FROM `"._DB_PREFIX_."instagramsync_images`";
            $imagenes = Db::getInstance()->executeS($sql);

            $sql = "SELECT p.id_product, pl.name, p.reference
                    FROM `"._DB_PREFIX_."product` p
                    LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON(p.id_product = pl.id_product)
                    WHERE p.active = 1";
            $products = Db::getInstance()->executeS($sql);

            $toret = '<div class="instagramimages panel">';
            $toret .= '<div class="panel-heading">'.$this->l('List of images').'</div>';
            $toret .= '<form method="post" action="" >';
            foreach ($imagenes as $imagen) {

                $toret .= '<div class="formimageline panel">';
                $toret .= '<div class="row">';
                $toret .= '<div class="col-xs-4">';
                $toret .= '<input type="hidden" name="id_image[]" value="'.$imagen['instagramsync_images_id'].'"/>';
                $toret .= '<img src="'._MODULE_DIR_.$this->name."/images/".$imagen['instagram_id']."/thumbnail.jpg".'" />';
                $toret .= '</div><div class="col-xs-6">';
                $toret .= '<select name="product_ids[]">';
                $toret .= '<option value=""> - </option>';
                foreach($products as $p){
                    $toret .= '<option value="'.$p['id_product'].'">'.$p['reference'].' - '.$p['name'].'</option>';
                }
                $toret .= '</select>';
                $toret .= '</div><div class="col-xs-2">';
                $toret .= '<label for="active_'.$imagen['instagramsync_images_id'].'">'.$this->l('Mostrar').'</label><input type="checkbox" name="active_'.$imagen['instagramsync_images_id'].'" id="active_'.$imagen['instagramsync_images_id'].'" />';
                $toret .= '</div>';
                $toret .= '</div>';
                // $toret .= '<pre>';
                // $toret .= print_r($imagen, true);
                // $toret.= "</pre>";

                $toret .= '</div>';
            }
            $toret .= '<button type="submit" value="1" id="configuration_form_submit_btn" name="submitblockinstasync_associations" class="btn btn-default pull-right">
							<i class="process-icon-save"></i> '.$this->l('Guardar').'
						</button>';
            $toret .= '</form>';
            $toret .= '</div>';

            return $toret;
        }

    }


 ?>
