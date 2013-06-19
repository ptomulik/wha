format 74

classcanvas 128002 class_ref 128514 // WHA_FileAnnotations
  draw_all_relations default hide_attributes default hide_operations default hide_getset_operations default show_members_full_definition default show_members_visibility default show_members_stereotype default show_members_context default show_members_multiplicity default show_members_initialization default show_attribute_modifiers default member_max_width 0 show_parameter_dir default show_parameter_name default package_name_in_tab default class_drawing_mode default drawing_language default show_context_mode default auto_label_position default show_relation_modifiers default show_relation_visibility default show_infonote default shadow default show_stereotype_properties default
  xyz 518 14 2000
end
classcanvas 128386 class_ref 128642 // WHA_File
  draw_all_relations default hide_attributes default hide_operations default hide_getset_operations default show_members_full_definition default show_members_visibility default show_members_stereotype default show_members_context default show_members_multiplicity default show_members_initialization default show_attribute_modifiers default member_max_width 0 show_parameter_dir default show_parameter_name default package_name_in_tab default class_drawing_mode default drawing_language default show_context_mode default auto_label_position default show_relation_modifiers default show_relation_visibility default show_infonote default shadow default show_stereotype_properties default
  xyz 301 221 2000
end
classcanvas 128642 class_ref 135170 // WHA_Dir
  draw_all_relations default hide_attributes default hide_operations default hide_getset_operations default show_members_full_definition default show_members_visibility default show_members_stereotype default show_members_context default show_members_multiplicity default show_members_initialization default show_attribute_modifiers default member_max_width 0 show_parameter_dir default show_parameter_name default package_name_in_tab default class_drawing_mode default drawing_language default show_context_mode default auto_label_position default show_relation_modifiers default show_relation_visibility default show_infonote default shadow default show_stereotype_properties default
  xyz 296 97 2000
end
classcanvas 128898 class_ref 135298 // WHA_Path
  draw_all_relations default hide_attributes default hide_operations default hide_getset_operations default show_members_full_definition default show_members_visibility default show_members_stereotype default show_members_context default show_members_multiplicity default show_members_initialization default show_attribute_modifiers default member_max_width 0 show_parameter_dir default show_parameter_name default package_name_in_tab default class_drawing_mode default drawing_language default show_context_mode default auto_label_position default show_relation_modifiers default show_relation_visibility default show_infonote default shadow default show_stereotype_properties default
  xyz 91 176 2000
end
relationcanvas 128514 relation_ref 128002 // <aggregation>
  from ref 128386 z 2001 to ref 128002
  role_a_pos 451 220 3000 role_b_pos 373 220 3000
  multiplicity_a_pos 501 247 3000 multiplicity_b_pos 373 247 3000
end
relationcanvas 129026 relation_ref 128258 // <aggregation>
  from ref 128386 z 2001 to ref 128898
  role_a_pos 187 220 3000 no_role_b
  multiplicity_a_pos 187 247 3000 no_multiplicity_b
end
relationcanvas 129154 relation_ref 128386 // <composition>
  geometry VHr
  from ref 128642 z 2001 to point 133 114
  line 129410 z 2001 to ref 128898
  role_a_pos 145 154 3000 no_role_b
  multiplicity_a_pos 118 154 3000 no_multiplicity_b
end
relationcanvas 129538 relation_ref 128514 // <association>
  from ref 128898 z 2001 to point 40 238
  line 129666 z 2001 to point 40 311
  line 129922 z 2001 to point 108 321
  line 129794 z 2001 to ref 128898
  role_a_pos 121 320 3000 no_role_b
  multiplicity_a_pos 81 320 3000 multiplicity_b_pos 75 250 3000
end
end
