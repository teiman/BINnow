<?php

/**
 * Ayuda para creacion de listas desplegables sobre elementos del sistema
 *
 * @package binow
 */




function genComboLocationsVisible($idquien=-1){

	return $out;
}




function genComboLocations($idquien=-1){


	return $out;
}


function genComboEtiquetas($idquien=-1){


	return $out;
}


function genComboTipoEtiqueta($idquien=-1){


	return $out;
}




function genComboProfiles($idquien=-1, $especifica=false){

	if ($especifica) {
		$extra = " isgroupprofile='".$especifica["id"]."' AND ";
	}

	$sql = "SELECT * FROM `profiles` WHERE $extra deleted=0 ORDER BY `name` ASC ";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["name"];

		$key = $row["id_profile"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}



function genCombosStatusCanal($canal,$idquien=-1,$exclusion=false){


	return $out;
}


function genCombosStatus($idquien=-1){


	return $out;
}



function genComboTarea($idquien=-1){


	return $out;
}



function genComboMedios($idquien){


	return $out;
}



function genComboGrupos($idquien,$ocultalocales=true){


    if($ocultalocales){
        $extra = "WHERE groups.id_location=0 ";
    }

	$sql = "SELECT * FROM `groups` $extra ORDER BY `group` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["group"];

		$key = $row["id_group"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$out .= "<option value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}


function genComboGruposDelegaciones($idquien){



	return $out;
}




function getComboStatus($id_label_type=3,$idquien=-1){

	$sql = "SELECT * FROM `labels` WHERE id_label_type='$id_label_type' ORDER BY `label` ASC";

	$res = query($sql);

	$out = "";
	while ($row= Row($res)){
		$name = $row["label"];

		$key = $row["id_label"];

		if ($key == $idquien){
			$selected = "selected='selected'";
		} else {
			$selected = "";
		}

		$icon = $row["icon"];
		$css = $icon?"background-image: url(icons/$icon);background-repeat: no-repeat":"";

		$out .= "<option class='relativo_tipo_".$id_label_type."' style='$css;padding-left: 18px' value='$key' $selected>" . html($name) . "</option>\n";
	}

	return $out;
}





function genComboCanales($idquien){


	return $out;
}


function genComboCOMDIR($com_dir ){


	return $out;
}


function genSelectorNubeEtiquetas($id_label_actual,$id_usuario_actual, $namelabel ){


	return $out;
}




function genArrayEtiquetas($id_comm, $modo="sinfiltro"){


	return $etiquetas;
}



function genListEtiquetasCommArray($id_comm, $filter=false){


	return $etiquetas;
}


function genListEtiquetasComm($id_comm){


	return $out;
}


