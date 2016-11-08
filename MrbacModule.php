<?php

namespace mrbac;

use Yii;
use mrbac\components\Configs;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;

class MrbacModule extends \yii\base\Module
{
    public $controllerNamespace = 'mrbac\controllers';
    /**
     * @inheritdoc
     */
    public $defaultRoute = 'authitem';
//    public $defaultRoute = 'assignment';

    /**
     * @var array
     * @see [[items]]
     */
    private $_menus = [];

    /**
     * @var array
     * @see [[items]]
     */
    private $_coreItems = [
        'authitem/manager' => '授权项列表',
        'authitem/auto' => '自动建立授权项',
        'authitem/assign' => '分配授权项',
        'authitem/edit-allowed' => '编辑总是允许',
    ];

    /**
     * @var array
     * @see [[items]]
     */
    private $_normalizeMenus;

    /**
     * Nav bar items
     * @var array
     */
    public $navbar;

    /**
     * @var string Main layout using for module. Default to layout of parent module.
     * Its used when `layout` set to 'left-menu', 'right-menu' or 'top-menu'.
     */
//    public $mainLayout = '@mrbac/views/layouts/main.php';

    public $userClassName;
    public $idField = 'id';
    public $usernameField = 'username';
    public $searchClass;

    public $debug=false;

    public $layout = '@mrbac/views/layouts/left-menu.php';

    public $alwaysAllows = array();
    public $allowedIPs =  ['127.0.0.1', '::1'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset(Yii::$app->i18n->translations['rbac-admin'])) {
            Yii::$app->i18n->translations['rbac-admin'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@mrbac/messages'
            ];
        }
        //user did not define the Navbar?
        if ($this->navbar === null) {
            $this->navbar = [
//                ['label' => Yii::t('rbac-admin', 'Help'), 'url' => 'https://github.com/mdmsoft/yii2-admin/blob/master/docs/guide/basic-usage.md'],
                ['label' => 'Application', 'url' => Yii::$app->homeUrl]
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->allowIps();

        return true;
    }

    public function allowIps(){
        if (Yii::$app instanceof \yii\web\Application && !$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }
    }
    /**
     * @return boolean whether the module can be accessed by the current user
     */
    protected function checkAccess()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        Yii::warning('Access to Gii is denied due to IP address restriction. The requested IP is ' . $ip, __METHOD__);

        return false;
    }

    /**
     * Get avalible menu.
     * @return array
     */
    public function getMenus()
    {
        if ($this->_normalizeMenus === null) {
            $mid = '/' . $this->getUniqueId() . '/';
            // resolve core menus
            $this->_normalizeMenus = [];
            $config = Configs::instance();
            foreach ($this->_coreItems as $id => $lable) {
                if ($id !== 'menu' || ($config->db !== null && $config->db->schema->getTableSchema($config->menuTable) !== null)) {
                    $this->_normalizeMenus[$id] = ['label' => Yii::t('rbac-admin', $lable), 'url' => [$mid . $id]];
                }
            }

            foreach (array_keys($this->controllerMap) as $id) {
                $this->_normalizeMenus[$id] = ['label' => Yii::t('rbac-admin', Inflector::humanize($id)), 'url' => [$mid . $id]];
            }

            // user configure menus
            foreach ($this->_menus as $id => $value) {
                if (empty($value)) {
                    unset($this->_normalizeMenus[$id]);
                } else {
                    if (is_string($value)) {
                        $value = [
                            'label' => $value,
                        ];
                    }
                    $this->_normalizeMenus[$id] = isset($this->_normalizeMenus[$id]) ? array_merge($this->_normalizeMenus[$id], $value) : $value;
                    if (!isset($this->_normalizeMenus[$id]['url'])) {
                        $this->_normalizeMenus[$id]['url'] = [$mid . $id];
                    }
                }
            }
        }
        return $this->_normalizeMenus;
    }

    /**
     * Set or add avalible menu.
     * @param array $menus
     */
    public function setMenus($menus)
    {
        $this->_menus = array_merge($this->_menus, $menus);
        $this->_normalizeMenus = null;
    }

    /**
     * @return array  总是允许的权限
     */
    public function getAlwaysAllowed(){

        return $this->alwaysAllows;
    }
}
