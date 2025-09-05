/*
Copyright Franck Theeten, 2025

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.

*/

var ajax_routes={};

$(document).ready(
	function()
	{
		//console.log("test jquery");
		$(".template-select").change(
			function()
			{
				console.log("test jquery");
				let val=$(this).val();
				if(val>=0)
				{
					console.log(val);
					let url=$(this).attr("data-event-route");
					let target_select=$(this).attr("data-event-property");
					console.log(url);
					
						$.ajax({
						  url: url,
						  data: {"template_id": val},
						  success: function(result)
									{									
										console.log(result);	
										let select_properties=$(target_select);
										select_properties.find('option').not(':first').remove();
										$.each(result, function (i, item) 
										{
											select_properties.append($('<option>', { 
												value: item.id,
												text : item.label 
											}));
										});
							
									},
						  dataType: "json"
						});
				}
				
			}
		);
		
		
		$("#add-target-property").click(
			function()
			{
				console.log("add prop");
				let template_src=$("#src-template-select").find(":selected").val();
				let template_src_label=$("#src-template-select").find(":selected").text();
				let property_src=$("#src-property-select").find(":selected").val();
				let property_src_label=$("#src-property-select").find(":selected").text();
				
				let template_target=$("#target-template-select").find(":selected").val();
				let template_target_label=$("#target-template-select").find(":selected").text();
				
				
				template_src=parseInt(template_src);
				property_src=parseInt(property_src);
				template_target=parseInt(template_target);
				if(template_src>=0 && property_src>=0 && template_target>0)
				{
					
					let l_dict={
						"template_src":template_src,
						"property_src":property_src,
						"template_target":template_target,
						
					}
					let row="<tr><td class='small'></td><td>"+template_src_label+"</td><td>"+property_src_label+"</td><td>"+template_target_label+"</td><td><a  class='o-icon-delete delete_new_row'  title='Delete'></a></td><td style='display:none;'><input type='hidden' name='constraint_params[]' value='"+JSON.stringify(l_dict)+"' ></td></tr>";
					$("#t_constraints tbody").append(row);
					
				}
				
			}
		);
		
		$(document).on('click','.delete_new_row',
			function()
			{
				console.log("delete new row");
				$(this).closest('tr').remove();
				
			}
		);
	}
);