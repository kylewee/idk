<!-- handle chart -->
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

if($app_module_path=='ext/funnelchart/view')
{
	$chart_settins = ($funnelchart_type[$reports['id']]=='funnel' ? "type: 'funnel'" :  "type: 'bar'");
	$click_event = 'funnelchart_items_listin(this.options.name,this.options.filter_by)';	
}
else
{
	$chart_settins = ($funnelchart_type[$reports['id']]=='funnel' ? "type: 'funnel'" : "type: 'bar'");
	$click_event = '';
}


if(count($data_css))
{
    echo '
    <style>
        ' . implode("\n",$data_css). '
    </style>    
        ';
}
?>

<script>
$(function(){
	
	$('#funnelchart_container_<?php echo $reports['id'] ?>').highcharts({
		chart: {
			<?php echo $chart_settins ?>,	
                        styledMode: true
		},
		title: {
			text: '<?php echo TEXT_EXT_TOTAL . ': ' . $count_items ?>',
			x: -50
		},
		yAxis:{
			title: {
        text: '',      
    	},
		},		
		plotOptions: {
			series: {
				dataLabels: {
					enabled: true,
					format: '{point.name}: {point.y:,.0f}',
					color:  'black',
					softConnector: true
				},
				cursor: 'pointer',
                                point: {
                                    events: {
                                        click: function() {
                                                <?php echo $click_event ?>                  
                                        }
                                    }
                                },
				neckWidth: '30%',
				neckHeight: '10%',
                                width: '70%',
                                center: ['40%', '50%'],

						//-- Other available options
				// height: pixels or percent
				// width: pixels or percent
			}
		},
		legend: {
			enabled: false
		},
		tooltip: {
                    formatter: function() {
                        return '<b>' + this.point.name + ': '  + this.y + '</b><br><?php echo TEXT_EXT_CONVERSION ?>: '+this.point.conversion <?php echo $sum_js_tip ?>;
                    }
                },
		series: [{
			name: '',
			data: [
					<?php echo implode(',',$data_js) ?>
			]
		}		
		]
	});

})

</script>