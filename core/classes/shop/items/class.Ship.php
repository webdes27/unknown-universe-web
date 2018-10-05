<?php
namespace shop;

use DB\MySQL;

class Ship extends AbstractItem
{
    function __construct($ItemData, MySQL $MySQL)
    {
       $this->mysql = $MySQL;

       $this->setITEMDATA($ItemData);

       $this->ID = $ItemData['ship_id'];
       $this->NAME = $ItemData['name'];
       $this->LOOT_ID = $ItemData['ship_lootid'];
       $this->PRICE = $ItemData['price_cre'] == 0 ?  $ItemData['price_uri'] :  $ItemData['price_cre'];
       $this->CURRENCY = $ItemData['price_cre'] != 0 ? 1 :  2;

       global $System;
        if($this->LOOT_ID == 'ship_aegis' || $this->LOOT_ID == 'ship_citadel'  || $this->LOOT_ID == 'ship_spearhead'){
            $this->LOOT_ID .= '-'.$System->User->getFactionName();
       }
       if($System->User->hasPremium() && $this->CURRENCY == 2){
            $this->PRICE = $this->PRICE * 0.8;
       }

       $this->DESCRIPTION = "";
       $this->AMOUNT_SELECTABLE = false;
       $this->IMAGE_URL = \Utils::getPathByLootId($this->LOOT_ID, '100x100');
       $this->SHOP_IMAGE_URL = \Utils::getPathByLootId($this->LOOT_ID, 'shop');

       $this->ATTRIBUTES = [
            "Hitpoints" => number_format($ItemData['ship_hp'], 0, '.', '.'),
            "Laser" => $ItemData['laser'],
            "Launcher" => $ItemData['heavy'],
            "Generator" => $ItemData['generator'],
            "Speed" => $ItemData['base_speed'],
            "Extras" => $ItemData['extra'],
            "Batteries" => number_format($ItemData['batteries'], 0, '.', '.'),
            "Rockets" => $ItemData['rockets'],
            "Cargo" => $ItemData['cargo'],
       ];
    }

    public function buy($UserID, $PlayerID, $Amount = 1)
    {
        if($this->CURRENCY == 1){
            $this->mysql->QUERY('UPDATE player_data SET CREDITS = CREDITS - ? WHERE PLAYER_ID  = ? AND USER_ID = ?', array($this->PRICE, $PlayerID, $UserID));
        }else{
            $this->mysql->QUERY('UPDATE player_data SET URIDIUM = URIDIUM - ? WHERE PLAYER_ID  = ? AND USER_ID = ?', array($this->PRICE, $PlayerID, $UserID));
        }

        global $System;
        $Hangar_Count = $System->User->Hangars->getHangars(true);
        $SHIP_DATA = $this->getITEMDATA();
        return $this->mysql->QUERY(
            'INSERT INTO player_hangar (USER_ID, PLAYER_ID, SHIP_ID, SHIP_DESIGN, SHIP_HP, HANGAR_COUNT) VALUES (?,?,?,?,?,?)',
            array($UserID, $PlayerID, $this->ID, $this->ID, $SHIP_DATA['ship_hp'], $Hangar_Count)
        );
    }
}