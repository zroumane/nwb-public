<?php

namespace App\Entity;

Class EntityParser{

  private $weaponLocal;
  private $weapons;
  private $pwd;

  public function __construct() {
    $this->weaponLocal = [];
    $this->weapons = [];
  }

  public function setWeaponLocal($local, $pwd){
    $this->weaponLocal = (array)json_decode(file_get_contents($pwd . '/public/json/'.$local.'/weapon.json'));
  }

  public function getWeaponLocal(){
    return $this->weaponLocal;
  }
  
  public function setWeapons($weapons)
  {
    foreach ($weapons as $index => $weapon) {
      $id = $weapon->getId();
      $weaponKey = $weapon->getWeaponKey();
      try{
        $this->weapons[$id] = $this->weaponLocal[$weaponKey];
      }
      catch(\Throwable $th){
        $this->weapons[$id] = $weaponKey;
      }  
    }
  }

  public function getWeapons(){
    return $this->weapons;
  }
  
  public function parseBuild($build){
    $build['weapons'] = array_map(function($w){
      if(!$w){
        return $w;
      }
      $id = intval(preg_replace('/[^0-9]+/', '', $w), 10);
      // $a = array_values(array_filter($this->weapons, fn($w) => $w->getId() == $id));
      // $weapon = $a[0];
      return $this->weapons[$id];
    }, $build['weapons']);
    return $build;
  }
  
}