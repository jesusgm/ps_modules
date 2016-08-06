<?php
  if (!defined('_PS_VERSION_'))
    exit;

  class BlockLazyLoad extends Module{
    public function __construct(){
      $this->name = 'blocklazyload';
      $this->tab = 'front_office_features';
      $this->version = '0.0.1';
      $this->author = 'Jesús Gándara';
      $this->need_instance = 0;
      $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
      $this->bootstrap = true;

      parent::__construct();

      $this->displayName = $this->l('Lazy Load');
      $this->description = $this->l('Adds lazy load for yout prestashop.');

      $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install(){
      if (Shop::isFeatureActive())
        Shop::setContext(Shop::CONTEXT_ALL);

      if (!parent::install() || !$this->registerHook('header') || !$this->registerHook('footer'))
        return false;

      Configuration::updateValue('selector', 'img');
      Configuration::updateValue('excludeselector', '.bx-viewport img');
      Configuration::updateValue('trigger_event', '');
      Configuration::updateValue('effect', 'fadeIn');
      Configuration::updateValue('threshold', '0');
      Configuration::updateValue('timeout', '0');

      return true;
    }

    public function uninstall(){
      if (!parent::uninstall())
        return false;

      Configuration::deleteByName('selector');
      Configuration::deleteByName('excludeselector');
      Configuration::deleteByName('trigger_event');
      Configuration::deleteByName('effect');
      Configuration::deleteByName('threshold');
      Configuration::deleteByName('timeout');

      return true;
    }

    public function hookDisplayHeader(){
      Media::addJSDef(array(
        'img_selector' => Configuration::get('selector'),
        'img_exclude_selector' => Configuration::get('excludeselector'),
        'lz_event' => Configuration::get('trigger_event') ? Configuration::get('trigger_event'): '',
        'lz_effect' => Configuration::get('effect') ? Configuration::get('effect') : '',
        'lz_threshold' => Configuration::get('threshold') ? Configuration::get('threshold') : '0',
        'lz_timeout' => Configuration::get('timeout') ? Configuration::get('timeout') : '0',
        'module_lazy_path' => $this->_path
      ));
      $this->context->controller->addJS(($this->_path).'views/js/jquery.lazyload.min.js');
      $this->context->controller->addJS(($this->_path).'views/js/lazyload.js');
    }

    public function getContent(){
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)){
            $selector = strval(Tools::getValue('selector'));
            $excludeselector = strval(Tools::getValue('excludeselector'));
            $trigger_event = strval(Tools::getValue('trigger_event'));
            $effect = strval(Tools::getValue('effect'));
            $threshold = strval(Tools::getValue('threshold'));
            $timeout = strval(Tools::getValue('timeout'));
            if (!$selector
              || empty($selector)
              || !Validate::isGenericName($selector)
              || !Validate::isGenericName($excludeselector)
              )
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            else
            {
                Configuration::updateValue('selector', $selector);
                Configuration::updateValue('excludeselector', $excludeselector);
                Configuration::updateValue('trigger_event', $trigger_event);
                Configuration::updateValue('effect', $effect);
                Configuration::updateValue('threshold', $threshold ? $threshold : 0);
                Configuration::updateValue('timeout', $timeout ? $timeout : 0);

                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }


    public function displayForm(){
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $events = array(
          array(
            'id_option' => '',       // The value of the 'value' attribute of the <option> tag.
            'name' => 'Default(scroll)'    // The value of the text content of the  <option> tag.
          ),
          array(
            'id_option' => 'mouseover',
            'name' => 'Mouseover'
          ),
          array(
            'id_option' => 'click',
            'name' => 'Click'
          ),
        );

        $effects = array(
          array(
            'id_option' => 'show',       // The value of the 'value' attribute of the <option> tag.
            'name' => 'Default(show)'    // The value of the text content of the  <option> tag.
          ),
          array(
            'id_option' => 'fadeIn',
            'name' => 'fadeIn'
          )
        );

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Configuración'),
            ),
            'input' => array(
                array(
                  'type' => 'textarea',
                  'label' => $this->l('Selector de imágenes a aplicar lazyload'),
                  'name' => 'selector',
                  'hint' => 'Introduce los selectores para jQuery separados por comas',
                  'required' => true
                ),
                array(
                  'type' => 'textarea',
                  'label' => $this->l('Selector de las imágenes a excluir'),
                  'name' => 'excludeselector',
                  'hint' => 'Introduce los selectores para jQuery separados por comas',
                  'required' => false
                ),
                array(
                  'type' => 'text',
                  'label' => $this->l('Threshold'),
                  'name' => 'threshold',
                  'hint' => 'Espacio (en px) para que se carguen las imágenes antes de entrar en el viewport',
                  'required' => false
                ),
                array(
                  'type' => 'text',
                  'label' => $this->l('Timeout'),
                  'name' => 'timeout',
                  'hint' => 'Especifica un timeout (en milisegundos) despues del cual se cargan TODAS las imágenes',
                  'required' => false
                ),
                array(
                  'type' => 'select',
                  'label' => $this->l('Evento para lanzar la carga de imágenes'),
                  'hint' => 'Por defecto se cargan cuando el usuario hace scroll y la imagen está en el viewport pero se puede elegir otro evento',
            		  'name' => 'trigger_event',
            		  'required' => true,
            		  'options' => array(
              			'query' => $events,
              			'id' => 'id_option',
                    'name' => 'name'
            		  )
                ),
                array(
                  'type' => 'select',
                  'label' => $this->l('Efecto cuando aparece la imagen lazy'),
                  'hint' => 'Por defecto se cargan con show',
            		  'name' => 'effect',
            		  'required' => true,
            		  'options' => array(
              			'query' => $effects,
              			'id' => 'id_option',
                    'name' => 'name'
            		  )
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
        $helper->fields_value['selector'] = Configuration::get('selector');
        $helper->fields_value['excludeselector'] = Configuration::get('excludeselector');
        $helper->fields_value['trigger_event'] = Configuration::get('trigger_event');
        $helper->fields_value['effect'] = Configuration::get('effect');
        $helper->fields_value['threshold'] = Configuration::get('threshold');
        $helper->fields_value['timeout'] = Configuration::get('timeout');

        return $helper->generateForm($fields_form);
    }

  }
 ?>
