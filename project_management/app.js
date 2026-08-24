(function(){
  function openDialog(id){var dialog=document.getElementById(id);if(dialog&&dialog.showModal)dialog.showModal();}
  function fill(form,data){Object.keys(data||{}).forEach(function(key){var field=form.elements[key];if(!field)return;var value=data[key]===null?'':data[key];if(field.type==='datetime-local'&&value)value=String(value).replace(' ','T').slice(0,16);field.value=value;});}
  function syncTemplateTypes(form,preferred){
    if(!form||!form.elements.node_type||!form.elements.parent_id)return;
    var library=form.elements.library.value;
    var parentSelect=form.elements.parent_id;
    var parentOption=parentSelect.options[parentSelect.selectedIndex];
    var parentType=parentOption?parentOption.getAttribute('data-node-type'):'ROOT';
    var fixedType=form.getAttribute('data-fixed-node-type');
    var allowed;
    if(fixedType)allowed=[fixedType];
    else if(library==='PROJECT'&&(parentType==='ROOT'||parentType==='FOLDER'))allowed=['FOLDER','PROJECT'];
    else if(library==='TASK'&&(parentType==='ROOT'||parentType==='FOLDER'))allowed=['FOLDER','TASK'];
    else allowed=['TASK'];
    var typeSelect=form.elements.node_type;var first='';
    for(var i=0;i<typeSelect.options.length;i++){var option=typeSelect.options[i];var enabled=allowed.indexOf(option.value)!==-1;option.disabled=!enabled;option.hidden=!enabled;if(enabled&&!first)first=option.value;}
    typeSelect.value=allowed.indexOf(preferred)!==-1?preferred:first;
  }
  function directChild(node,className){var children=node.children;for(var i=0;i<children.length;i++){if(children[i].classList.contains(className))return children[i];}return null;}
  function setTreeNode(node,collapsed){var row=directChild(node,'tree-row');var children=directChild(node,'tree-children');var toggle=row?row.querySelector('.tree-toggle'):null;node.classList.toggle('collapsed',collapsed);if(children)children.style.display=collapsed?'none':'';if(toggle){toggle.setAttribute('aria-expanded',collapsed?'false':'true');toggle.textContent=collapsed?'+':'−';toggle.title=collapsed?'展开':'折叠';}}
  var treeToggles=document.querySelectorAll('.tree-toggle');for(var toggleIndex=0;toggleIndex<treeToggles.length;toggleIndex++){treeToggles[toggleIndex].addEventListener('click',function(event){event.preventDefault();event.stopPropagation();var node=this.parentNode.parentNode;setTreeNode(node,!node.classList.contains('collapsed'));});}
  var treeActions=document.querySelectorAll('[data-tree-action]');for(var actionIndex=0;actionIndex<treeActions.length;actionIndex++){treeActions[actionIndex].addEventListener('click',function(event){event.preventDefault();var collapse=this.getAttribute('data-tree-action')==='collapse';var panel=this.closest('.tree-panel,[data-tab-panel]')||document;var nodes=panel.querySelectorAll('.template-node.has-children,.task-node.has-children');for(var nodeIndex=0;nodeIndex<nodes.length;nodeIndex++)setTreeNode(nodes[nodeIndex],collapse);});}
  document.addEventListener('click',function(event){
    var newTemplate=event.target.closest('[data-open-template]');if(newTemplate){var newTemplateForm=document.querySelector('#template-form form');if(newTemplateForm){newTemplateForm.reset();newTemplateForm.removeAttribute('data-fixed-node-type');newTemplateForm.elements.id.value='';newTemplateForm.elements.parent_id.value=newTemplate.getAttribute('data-parent-id')||'0';syncTemplateTypes(newTemplateForm,'');}openDialog('template-form');return;}
    var newTask=event.target.closest('[data-open-task]');if(newTask){var newTaskForm=document.querySelector('#task-form form');if(newTaskForm){newTaskForm.reset();newTaskForm.elements.id.value='';newTaskForm.elements.parent_id.value=newTask.getAttribute('data-parent-id')||'0';}openDialog('task-form');return;}
    var opener=event.target.closest('[data-open]');if(opener){openDialog(opener.getAttribute('data-open'));return;}
    var closer=event.target.closest('[data-close]');if(closer){var dialog=closer.closest('dialog');if(dialog)dialog.close();return;}
    var role=event.target.closest('[data-edit-role]');if(role){var rf=document.querySelector('#role-form form');fill(rf,JSON.parse(role.getAttribute('data-edit-role')));openDialog('role-form');return;}
    var template=event.target.closest('[data-edit-template]');if(template){var tf=document.querySelector('#template-form form');var templateData=JSON.parse(template.getAttribute('data-edit-template'));tf.setAttribute('data-fixed-node-type',templateData.node_type);fill(tf,templateData);syncTemplateTypes(tf,templateData.node_type);openDialog('template-form');return;}
    var task=event.target.closest('[data-edit-task]');if(task){var taskForm=document.querySelector('#task-form form');var taskData=JSON.parse(task.getAttribute('data-edit-task'));taskData.duration=taskData.planned_duration;fill(taskForm,taskData);openDialog('task-form');return;}
    var tab=event.target.closest('[data-tab]');if(tab){var root=tab.closest('.detail-panel')||document;root.querySelectorAll('[data-tab]').forEach(function(x){x.classList.toggle('active',x===tab);});root.querySelectorAll('[data-tab-panel]').forEach(function(x){x.hidden=x.getAttribute('data-tab-panel')!==tab.getAttribute('data-tab');});}
  });
  var templateParent=document.querySelector('#template-form select[name="parent_id"]');if(templateParent){templateParent.addEventListener('change',function(){syncTemplateTypes(this.form,this.form.elements.node_type.value);});}
  document.querySelectorAll('dialog').forEach(function(dialog){dialog.addEventListener('click',function(event){if(event.target===dialog)dialog.close();});});
})();
