<?php

$obj = array();

if(isset($_GET['id']))
{
  $obj = db_find('app_composite_unique_fields',$_GET['id']);  
}
else
{
  $obj = db_show_columns('app_composite_unique_fields');
  $obj['is_active']=1;        
}