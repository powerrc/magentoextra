/*
* this script is used for generating unique coupon in quantity with random string
* this script is inspired by this link http://stackoverflow.com/questions/11621194/create-magento-coupon-via-api
* for magento 1.x CE only
* put in under your magento root path, same folder level as "app"
* run it like this 
* #php gencoupon.php
*/

require_once 'app/Mage.php';
Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

umask(0);
Mage::app();

/*
*for example , to generate 20 coupons  with  prefix "SP" and random code .
*Total length of each code is 16 (14+2)
*The valid date is set from "2015-05-25" to "2015-06-30" , for websites id "32" and customer group id "0"
*/
 for($i=0;$i<20;$i++)
        {
          $code = 'SP'.$this->generateUniqueId('14');
          $this->generateRule($code,50,'Special Promotion with code:'.$code,'2015-05-24','2015-06-30',array(32),array(0));
          echo $code."<br/>";
        }


function generateRule($code, $amount, $label, $from_date = '', $to_date = '',$webiteIds,$customerGroupIds,$couponType='by_fixed',$name = ''){

    $name = (empty($name))? $label : $name;
    $labels[0] = $label;//default store label

/*
* the following example to to set the condition to be 
* valid only when there is a product sku "SKU123" in the cart
* to pass a null array , if you don't want any condition
*/
     $conditions = array(
        "1"         => array(
                "type"          => "salesrule/rule_condition_combine",
                "aggregator"    => "all",
                "value"         => "1",
                "new_child"     => null
            ),
        "1--1"      => array(
                "type"          => "salesrule/rule_condition_product_found",
                "aggregator"    => "all",
                "value"         => "1",
                "new_child"     => null
            ),
        "1--1--1"   => array(
                "type"          => "salesrule/rule_condition_product",
                "attribute"     => "sku",
                "operator"      => "==",
                "value"         => "SKU123"
            )
    );
   

    $coupon = Mage::getModel('salesrule/rule');
    $coupon->setName($name)
    ->setDescription($name)
    ->setFromDate($from_date)
    ->setToDate($to_date)
    ->setCouponCode($code)
    ->setUsesPerCoupon(1)
    ->setUsesPerCustomer(1)
    ->setCustomerGroupIds($customerGroupIds) //an array of customer grou pids
    ->setIsActive(1)
    ->setStopRulesProcessing(0)
    ->setIsAdvanced(1)
    ->setProductIds('')
    ->setSortOrder(0)
    ->setSimpleAction($couponType)
    ->setDiscountAmount($amount)
    ->setDiscountQty(null)
    ->setDiscountStep('0')
    ->setSimpleFreeShipping('0')
    ->setApplyToShipping('0')
    ->setIsRss(0)
    ->setWebsiteIds($webiteIds) //array of websites ids
    ->setCouponType(2)
    ->setStoreLabels($labels)
    ->setData('conditions',$conditions);
    ;
    $coupon->loadPost($coupon->getData());
    $coupon->save();
}


function getAllCustomerGroups(){
    //get all customer groups
    $customerGroupsCollection = Mage::getModel('customer/group')->getCollection();
    $customerGroupsCollection->addFieldToFilter('customer_group_code',array('nlike'=>'%auto%'));
//    $customerGroupsCollection->load();
    $groups = array();
    foreach ($customerGroupsCollection as $group){
    $groups[] = $group->getId();
    }
    return $groups;
}

function getAllWbsites(){
    //get all wabsites
    $websites = Mage::getModel('core/website')->getCollection();
    $websiteIds = array();
    foreach ($websites as $website){
    $websiteIds[] = $website->getId();
    }
    return $websiteIds;
}

function generateUniqueId($length = null){
    $rndId = crypt(uniqid(rand(),1));
    $rndId = strip_tags(stripslashes($rndId));
    $rndId = str_replace(array(".", "$"),"",$rndId);
    $rndId = strrev(str_replace("/","",$rndId));
    if (!is_null($rndId)){
        return strtoupper(substr($rndId, 0, $length));
    }
    return strtoupper($rndId);
}
    
