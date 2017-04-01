<?php

    if (!defined('_PS_VERSION_'))
        exit;

    require_once _PS_MODULE_DIR_ . "blockinstasync/classes/InstagramImage.php";


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

            if (!parent::install()
                || !$this->registerHook('header')
                || !$this->registerHook('instagramsync')
                || !Configuration::updateValue('BLOCKINSTASYNC_ACCESS_TOKEN', ''))
                    return false;

            return true;
        }

        public function uninstall(){
            if (!parent::uninstall() || !Configuration::deleteByName('BLOCKINSTASYNC_ACCESS_TOKEN'))
                return false;

            return true;
        }

        public function hookHeader(){
            //Load Style
            $this->context->controller->addCSS(($this->_path).'views/css/blockinstagramsync.css', 'all');
            $this->context->controller->addCSS(($this->_path).'views/css/owl.carousel.min.css', 'all');
            $this->context->controller->addCSS(($this->_path).'views/css/owl.theme.default.css', 'all');

            //Load JS
            $this->context->controller->addJS(($this->_path).'views/js/blockinstagramsync.js');
            // $this->context->controller->addJS(($this->_path).'views/js/masonry.pkgd.min.js');
            $this->context->controller->addJS(($this->_path).'views/js/isotope.pkgd.min.js');
            $this->context->controller->addJS(($this->_path).'views/js/owl.carousel.min.js');
        }
        public function hookInstagramsync(){
            $this->context->smarty->assign(array(
                'images' => InstagramImage::getInstagramImages(),
                'img_base_path' => _MODULE_DIR_.$this->name."/images/",
            ));

            return $this->display(__FILE__, 'blockinstagramsync.tpl');
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
                //Delete associations
                $truncate ="TRUNCATE TABLE `"._DB_PREFIX_."instagramsync_image_product`";
                Db::getInstance()->execute($truncate);

                //Asociate the products and the images
                foreach (Tools::getValue('product_ids') as $id_image => $products) {
                    foreach ($products as $id_product) {
                        $insert = "INSERT INTO `"._DB_PREFIX_."instagramsync_image_product`
                                    VALUES(
                                        0,
                                        ".$id_image.",
                                        ".$id_product."
                                    )";

                        // echo $insert . "<br/>";
                        Db::getInstance()->execute($insert);
                    }
                }
                // die();
                //Set the shown field
                $update = "UPDATE `"._DB_PREFIX_."instagramsync_images` SET shown = 0";
                Db::getInstance()->execute($update);
                foreach (Tools::getValue('active') as $id_image => $products) {
                    $update = "UPDATE `"._DB_PREFIX_."instagramsync_images`
                                SET shown = 1
                                WHERE instagramsync_images_id = " . $id_image;
                    Db::getInstance()->execute($update);
                }
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
                $caption = $img['caption']['text'];
                $username = $img['user']['full_name'];
                $latitude = $img['location']['latitude'];
                $longitude = $img['location']['longitude'];
                $location_name = $img['location']['name'];
                $likes = $img['likes']['count'];

                $ins_img = new InstagramImage(InstagramImage::getIdByInstagramId($id));
                if(empty($ins_img->shown)){
                    $ins_img->shown = 0;    //sync new images hidden
                }
                $ins_img->caption = $caption;
                $ins_img->instagram_id = $id;
                $ins_img->instagram_link = $link;
                $ins_img->instagram_user_name = $username;
                $ins_img->latitude = $latitude;
                $ins_img->longitude = $longitude;
                $ins_img->likes = $likes;

                $ins_img->save();


                // $insert = 'INSERT INTO `'._DB_PREFIX_.'instagramsync_images`
                //             VALUES(
                //                 0,
                //                 0,
                //                 "' . $caption . '",
                //                 "' . $id . '",
                //                 "' . $link . '",
                //                 "' . $username . '",
                //                 "' . $latitude . '",
                //                 "' . $longitude . '",
                //                 "' . $location_name . '"
                //                 "' . $likes . '",
                //             )';
                // Db::getInstance()->execute($insert);

                $img_dir = __DIR__.'/images/'. $id;

                if(!file_exists($img_dir)){
                    mkdir($img_dir, 0755, true);
                }
                //Download standard
                if(!file_exists($img_dir.'/standard_resolution.jpg') || filesize($img_dir.'/standard_resolution.jpg') == 0){
                    error_log("Descargo standard_resolution de " . $img_dir);
                    $standard_resolution = file_get_contents($img['images']['standard_resolution']['url']);
                    file_put_contents($img_dir.'/standard_resolution.jpg', $standard_resolution);
                }
                // Download low_resolution
                if(!file_exists($img_dir.'/low_resolution.jpg') || filesize($img_dir.'/low_resolution.jpg') == 0){
                    error_log("Descargo low_resolution de " . $img_dir);
                    $low_resolution = file_get_contents($img['images']['low_resolution']['url']);
                    file_put_contents($img_dir.'/low_resolution.jpg', $low_resolution);
                }
                //Download thumbnail
                if(!file_exists($img_dir.'/thumbnail.jpg') || filesize($img_dir.'/thumbnail.jpg') == 0){
                    error_log("Descargo thumbnail de " . $img_dir);
                    $thumbnail = file_get_contents($img['images']['thumbnail']['url']);
                    file_put_contents($img_dir.'/thumbnail.jpg', $thumbnail);
                }
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
                    LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON(p.id_product = pl.id_product AND pl.id_lang = ".$this->context->language->id.")
                    WHERE p.active = 1";
            $products = Db::getInstance()->executeS($sql);

            $this->smarty->assign(array(
                'products' => $products,
                'imagenes' => $imagenes,
                'img_base_path' => _MODULE_DIR_.$this->name."/images/",

            ));

            return $this->display(__FILE__, 'views/templates/admin/imagesform.tpl');
        }

    }


 ?>
