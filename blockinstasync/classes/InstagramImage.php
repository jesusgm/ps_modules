<?php
    /**
     * Instagram image class
     */
    class InstagramImage extends ObjectModel{
        public $shown;
        public $caption;
        public $instagram_id;
        public $instagram_link;
        public $instagram_user_name;
        public $latitude;
        public $longitude;
        public $location_name;
        public $likes;
        /**
         * @see ObjectModel::$definition
         */
        public static $definition = array(
            'table' => 'instagramsync_images',
            'primary' => 'instagramsync_images_id',
            'multilang' => true,
            'multilang_shop' => true,
            'fields' => array(
                /* Classic fields */
                'shown' =>                  array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
                'caption' =>                array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
                'instagram_id' =>           array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
                'instagram_link' =>         array('type' => self::TYPE_STRING, 'validate' => 'isAbsoluteUrl', 'size' => 255),
                'instagram_user_name' =>    array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
                'latitude' =>               array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
                'longitude' =>              array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
                'location_name' =>          array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
                'likes' =>                  array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            ),
        );

        public static function getIdByInstagramId($instagram_id){
            $select = "SELECT instagramsync_images_id
                        FROM `"._DB_PREFIX_."instagramsync_images`
                        WHERE instagram_id LIKE '".$instagram_id."'";
            return Db::getInstance()->getValue($select);
        }

        public static function getImageProducts($id_image){
            $sql = "SELECT id_product
                    FROM `"._DB_PREFIX_."instagramsync_image_product`
                    WHERE id_instagramsync_images = " . $imagen['instagramsync_images_id'];
            return Db::getInstance()->executeS($sql);
        }

        public static function getInstagramImages(){
            $select = "SELECT *
                        FROM `"._DB_PREFIX_."instagramsync_images` ii
                        WHERE ii.shown = 1 ";
            $images = Db::getInstance()->executeS($select);
            if(!empty($images)){
                foreach ($images as &$image) {
                    $select ="SELECT *
                            FROM `"._DB_PREFIX_."instagramsync_image_product` ip
                            LEFT JOIN `"._DB_PREFIX_."products` p ON(p.id_product = ip.id_product)
                            LEFT JOIN `"._DB_PREFIX_."product_lang` p ON(pl.id_product = p.id_product AMD pl.id_lang = ".Context::getContext()->language->id.")
                            WHERE ip.id_instagramsync_images = " . $image['instagramsync_images_id'];
                    $products = Db::getInstance()->executeS($select);
                    if(!empty($products)){
                        foreach ($products as $product) {
                            $image['products'][] = $product;
                        }
                    }                        
                }
            }

            return $images;
        }
    }

 ?>
