<?php

/**
 * Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 * https://www.rukovoditel.net.ru/
 * 
 * CRM Руководитель - это свободное программное обеспечение, 
 * распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 * Государственная регистрация программы для ЭВМ: 2023664624
 * https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */

class fields_choices_flowchart
{
	public $nodes;
	
	public $edges;
	
	public $height;
	
	public $height_step;
	
	public $y;
	
	public $y_step;
			
	function __construct()
	{
		$this->y = 20;
		$this->y_step = 70;
		
		$this->height = 0;
		$this->height_step = 150;
				
		$this->nodes = array();
		$this->edges = array();				
	}
	
	function prepare_data($fields_id)
	{				
		$tree = fields_choices::get_tree($fields_id);
	
		$previous_id = 0;
		
		foreach($tree as $v)
		{
			$filters_title = '';
			$reports_type = 'fields_choices' . $v['id'];
			$reports_info_query = db_query("select * from app_reports where entities_id='" . db_input($_GET['entities_id']). "' and reports_type='{$reports_type}'");
			if($reports_info = db_fetch_array($reports_info_query))
			{
				$filters_query = db_query("select rf.*, f.name, f.type from app_reports_filters rf left join app_fields f on rf.fields_id=f.id where rf.reports_id='" . db_input($reports_info['id']) . "' order by rf.id");
				while($filters = db_fetch_array($filters_query))
				{
					$filters_title .= fields_types::get_option($filters['type'],'name',$filters['name']) . ": " . reports::get_condition_name_by_key($filters['filters_condition'])  . ' ' . reports::render_filters_values($filters['fields_id'],$filters['filters_values'],', ',$filters['filters_condition']) . '<br>';
				}
			}
		
			$id = $v['id'];
		
			//handle nodes
			$this->nodes[] = "{ data: { id: 'choice_filter_{$id}',name: '" . addslashes($filters_title). "'}, classes:'choice_filter', position: { x: 0, y: {$this->y} }}";
			$this->nodes[] = "{ data: { id: 'choice_{$id}',name: '" . addslashes($v['name']). "'}, classes:'choice', position: { x: 90, y: {$this->y} }}";
		
			$this->y += $this->y_step;
		
			$this->height+=$this->height_step;
		
			//hande edges
			$this->edges[] = "{ data: { id: 'edge_{$id}_{$id}', source: 'choice_filter_{$id}', target: 'choice_{$id}',label: '" . addslashes(TEXT_YES). "'} }";
		
			if($previous_id>0)
			{
				$this->edges[] = "{ data: { id: 'edge_{$previous_id}_{$id}', source: 'choice_filter_{$previous_id}', target: 'choice_filter_{$id}',label: '" . addslashes(TEXT_NO). "'} }";
			}
		
			$previous_id = $id;
		}
	}
	
}