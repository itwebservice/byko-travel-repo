<?php
$moduleName = $_POST['module_name'];
$modules = json_decode(file_get_contents("modules.json"),true);
$subModules = json_decode(file_get_contents("sub_modules.json"),true);
$moduleId = 0;
$data = [];
foreach($modules as $module)
{
    if($moduleName == $module['name'])
    {
        $moduleId = $module['id'];
    }
}

foreach($subModules as $subModule)
{
    if($moduleId == $subModule['module_id'])
    {
            $data[] = $subModule;
    }
}

if(empty($data))
{
    $data = $subModules;
}

foreach($data as $val)
{
    echo "<option value='$val[name]'>";
}
