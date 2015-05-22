<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * Word 2000 export page
	 * The bugs displayed in print_all_bug_page.php are saved in a .doc file
	 * The IE icon allows to see or directly print the same result
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'string_api.php' );
	require_once( 'date_api.php' );

	auth_ensure_user_authenticated();

	$f_type_page	= gpc_get_string( 'type_page', 'word' );
	$f_search		= gpc_get_string( 'search', false ); /** @todo need a better default */
	$f_offset		= gpc_get_int( 'offset', 0 );
	$f_export		= gpc_get_string( 'export' );
	$f_show_flag	= gpc_get_bool( 'show_flag' );

	helper_begin_long_process();

	# word or html export
	if ( $f_type_page != 'html' ) {
		$t_export_title = helper_get_default_export_filename( '' );
		$t_export_title = preg_replace( '/[\/:*?"<>|]/', '', $t_export_title );
		$t_export_title .= '.doc';

		# Make sure that IE can download the attachments under https.
		header( 'Pragma: public' );

		header( 'Content-Type: application/msword' );

		http_content_disposition_header( $t_export_title );
	}

	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	$t_row_count = count( $result );

  function retornarCampoPersonalizado($cadena)
  {
      $cadena = "<tr><td colspan=\"2\" class=\"print\">".$cadena."</td></tr>";
      return $cadena;
  }
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<?php
	html_page_top1();
	html_head_end();
	html_body_begin();

	$f_bug_arr = explode( ',', $f_export );
	$t_count_exported = 0;
	$t_date_format = config_get( 'normal_date_format' );
	$t_short_date_format = config_get( 'short_date_format' );

	$t_lang_bug_view_title = lang_get( 'bug_view_title' );
	$t_lang_id = lang_get( 'id' );
	$t_lang_category = lang_get( 'category' );
	$t_lang_severity = lang_get( 'severity' );
	$t_lang_reproducibility = lang_get( 'reproducibility' );
	$t_lang_date_submitted = lang_get( 'date_submitted' );
	$t_lang_last_update = lang_get( 'last_update' );
	$t_lang_reporter = lang_get( 'reporter' );
	$t_lang_assigned_to = lang_get( 'assigned_to' );
	$t_lang_platform = lang_get( 'platform' );
	$t_lang_due_date = lang_get( 'due_date' );
	$t_lang_os = lang_get( 'os' );
	$t_lang_os_version = lang_get( 'os_version' );
	$t_lang_fixed_in_version = lang_get( 'fixed_in_version' );
	$t_lang_resolution = lang_get( 'resolution' );
	$t_lang_priority = lang_get( 'priority' );
	$t_lang_product_build = lang_get( 'product_build' );
	$t_lang_eta = lang_get( 'eta' );
	$t_lang_status = lang_get( 'status' );
	$t_lang_product_version = lang_get( 'product_version' );
	$t_lang_no_bugnotes_msg = lang_get( 'no_bugnotes_msg' );
	$t_lang_projection = lang_get( 'projection' );
	$t_lang_target_version = lang_get( 'target_version' );
	$t_lang_summary = lang_get( 'summary' );
	$t_lang_description = lang_get( 'description' );
	$t_lang_steps_to_reproduce = lang_get( 'steps_to_reproduce' );
	$t_lang_additional_information = lang_get( 'additional_information' );
	$t_lang_bug_notes_title = lang_get( 'bug_notes_title' );
	$t_lang_system_profile = lang_get( 'system_profile' );
	$t_lang_attached_files = lang_get( 'attached_files' );

	$t_current_user_id = auth_get_current_user_id();
	$t_user_bugnote_order = user_pref_get_pref ( $t_current_user_id, 'bugnote_order' );

	for( $j=0; $j < $t_row_count; $j++ ) {
		$t_bug = $result[$j];
		$t_id = $t_bug->id;

		if ( $j % 50 == 0 ) {
			# to save ram as report will list data once, clear cache after 50 bugs
			bug_text_clear_cache();
			bug_clear_cache();
			bugnote_clear_cache();
		}

		# display the available and selected bugs
		if ( in_array( $t_id, $f_bug_arr ) || !$f_show_flag ) {
			if ( $t_count_exported > 0 ) {
				echo "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
			}

			$t_count_exported++;

			$t_last_updated = date( $g_short_date_format, $t_bug->last_updated );

            # grab the project name
            $t_project_name = project_get_field( $t_bug->project_id, 'name' );
			$t_category_name = category_full_name( $t_bug->category_id, false );
?>
<br />
<table class="width100" cellspacing="0" border="0">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $t_project_name ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="2">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print-category">
	<td class="print" width="40%">
		<?php echo $t_lang_id.' '.$t_id ?>:
	</td>
	<td class="print">
		<?php echo $t_lang_last_update.' '.date( $t_date_format, $t_bug->last_updated ) ?>:
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="2">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php
    echo $t_lang_assigned_to;
    ?>:
	</td>
	<td class="print">
		<?php
			if ( access_has_bug_level( config_get( 'view_handler_threshold' ), $t_id ) ) {
				print_user_with_subject( $t_bug->handler_id, $t_id );
			}
		?>
	</td>
</tr>
<?php
  switch($t_bug->project_id){
      case '19': // Proyecto 5. Acta de conciliación
        $cadena = "<center><b>CONCILIADOR (A)</b></center>";
        echo retornarCampoPersonalizado($cadena);
        break;
      case '17':
        $cadena = "<center><b>CONCILIADOR (A)</b></center>";
        echo retornarCampoPersonalizado($cadena);
        break;
      case '20':
        $cadena = "<center><b>CONCILIADOR (A)</b></center>";
        echo retornarCampoPersonalizado($cadena);
        break;
      default:
        $cadena = "";
        echo retornarCampoPersonalizado($cadena);
        break;
  }
?>
<?php
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
foreach( $t_related_custom_field_ids as $t_custom_field_id ) {
	$t_def = custom_field_get_definition( $t_custom_field_id );
  // aorozco 2011-08-11
  // Obteniendo valor del campo para determinar si es N/A y ocultarlo
  // Adiviné el nombre del método y sus parámetros por cosas del destino :P
  $value = custom_field_get_value($t_custom_field_id, $t_id);
  //Campos ocultos en todos los proyectos
  $listado_campos = array('71', '72', '12', '11', '10', '9'); // Semana, Día, Sala, Turno, Medio de recepción
  //Campos ocultos en ciertos proyectos
  switch($t_bug->project_id){
    case '17': //Inasistencia
      $listado_campos[] = '73'; //Fecha en que debe regresar el usuario
      $listado_campos[] = '69'; //Reparto
      $listado_campos[] = '70'; //Tipo
      break;
    case '18': //No acuerdo
      $listado_campos[] = '73'; //Fecha en que debe regresar el usuario
      $listado_campos[] = '69'; //Reparto
      $listado_campos[] = '70'; //Tipo
      break;
    case '19': //Acta de conciliación
      $listado_campos[] = '73'; //Fecha en que debe regresar el usuario
      $listado_campos[] = '69'; //Reparto
      $listado_campos[] = '70'; //Tipo
      break;
    case '20': //Asunto no conciliable
      $listado_campos[] = '73'; //Fecha en que debe regresar el usuario
      $listado_campos[] = '69'; //Reparto
      $listado_campos[] = '70'; //Tipo
      break;
  }
  $mostrar_campo = !in_array($t_custom_field_id, $listado_campos);
  if(strtoupper($value) == 'N/A'){
    $mostrar_campo = false;
  }
  if($mostrar_campo){
?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>:
	</td>
	<td class="print" colspan="2">
		<?php print_custom_field_value( $t_def, $t_custom_field_id, $t_id ); ?>
	</td>
</tr>
<?php
  }
  // rjaramillo 2011-07-18
  // Agregado para poder imprimir texto personalizado después de determinados campos personalizados
  // Se agregó el switch($t_custom_field_id) completo
  // La tabla que se consulta para saber qué ID tiene cada campo personalizado es mantis_custom_field_table
  switch($t_custom_field_id){
    case '10':// Medio por el cual se enteró
      switch($t_bug->project_id){
        case '1': // Proyecto 1. Solicitudes Jurídico
          $cadena = "<center><b>DATOS DE LA CONTRAPARTE</b></center>";
          break;
        case '16': // Proyecto 2. Solicitudes Conciliación
        case '21': // Proyecto 3. Solicitudes Conciliación(varios)
          //$cadena = "Comedidamente solicito (solicitamos) a ustedes audiencia de conciliación en materia de:";
          $cadena = "quien(es) solicitó (solicitaron) audiencia de conciliación con el señor  (a) (los señores):";
          break;
        default:
          $cadena = "";
          break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '11':// Turno
      switch($t_bug->project_id){
        case '1': // Proyecto 1. Solicitudes Jurídico
          $cadena = "<center><b>DATOS DEL USUARIO</b></center>";
          break;
        case '16': // Proyecto 2. Solicitudes Conciliación
        case '21': // Proyecto 3. Solicitudes Conciliación(varios)
          $cadena = "<center><b>SOLICITUD DE CONCILIACIÓN<br>Datos del (de los) solicitante(s)</b></center>";
          break;
        default:
          $cadena = "";
        break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '84':// CC – Área o Materia
      $cadena = "Con el fin de solucionar el conflicto con el señor:<br><center><b>DATOS DEL CITADO</b></center>";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '75':// Escala del conflicto
      $cadena = "La última vez que alguien intervino en el conflicto fue:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '41':// Vivienda
      $cadena = "<center><b>DATOS LABORALES Y TRÁMITES HECHOS</b></center>";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '59':// Relación de documentos aportados y recibidos
      $cadena = "<p>EL USUARIO SE COMPROMETE A: a.) Suministrar oportunamente documentos, nombres, direcciones, demás datos y elementos que se requieran. b.) Sufragar los gastos* \"que demande la prestación del servicio. NOTA. El servicio que prestan los asesores y estudiantes del consultorio jurídico es \"gratuito\", es decir, \"no causa honorarios\".</p>
      <p>EL USUARIO AUTORIZA: Expresamente el Consultorio Jurídico para archivar el asunto en caso de no dar cumplimiento a los compromisos adquiridos en el párrafo anterior, exonerándolo de toda obligación derivada del poder otorgado y procediendo el apoderado al archivo del caso ante el Consultorio Jurídico.</p>
      <p>EL CONSULTORIO JURÍDICO LE HACE SABER AL USUARIO: a.) Recepcionar ésta entrevista no implica aceptación por parte del Consultorio Jurídico respecto de adelantar proceso o gestión, ya que estos se someterán a revisión y aprobación. b.) El Consultorio Jurídico podrá rechazar de inmediato el asunto si encuentra faltas a la verdad en el contenido de la entrevista o en los documentos aportados o por cualquier otra anomalía detectada.</p>";
      echo retornarCampoPersonalizado($cadena);
      $cadena = "
      <table width=\"80%\" align=\"center\">
        <tr>
          <td class=\"print\"><p>&nbsp;</p><center>__________________________<br>Firma usuario entrevistado</center></td>
          <td class=\"print\"><p>&nbsp;</p><center>__________________________<br>Estudiante entrevistador</center></td>
        </tr>
      </table>";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '73':// Fecha en que debe regresar el usuario
      switch($t_bug->project_id){
        case '1': // Proyecto 1. Solicitudes Jurídico
          $cadena = "<p>&nbsp;</p><center>__________________________<br>Firma asesor</center><p>*Gastos se refieren a : fotocopias, autenticaciones, etc.</p>";
          break;
        case '16': // Proyecto 2. Solicitudes Conciliación
        case '21': // Proyecto 3. Solicitudes Conciliación(varios)
          $cadena = "
          <table width='50%' align='center' border='0'>
            <tr class='print'>
              <td colspan='3' class='print'><b>NOTIFICACIONES:</b></td>
            </tr>
            <tr class='print'>
              <td colspan='3' class='print'>Las comunicaciones se pueden dirigir a la dirección del solicitante o solicitantes</td>
            </tr>
            <tr class='print'>
              <td colspan='3' valign='top' class='print'><b>Solicitante(s):</b></td>
            </tr>
            <tr class='print'>
              <td valign='top' class='print'>
                <span style='line-height:16px;' class='print'><b>Firma:</b>
                <br><br><br>__________________________
                <br>Nombre:
                <br>No. de identificaci&oacute;n:
                <br>Direcci&oacute;n:
                <br>Ciudad:</span>
              </td>
              <td class='print'>
                <span style='line-height:16px;' class='print'><b>Firma:</b>
                <br><br><br>__________________________
                <br>Nombre:
                <br>No. de identificaci&oacute;n:
                <br>Direcci&oacute;n:
                <br>Ciudad:</span>
              </td>
              <td class='print'>
                <span style='line-height:16px;' class='print'><b>Firma:</b>
                <br><br><br>__________________________
                <br>Nombre:
                <br>No. de identificaci&oacute;n:
                <br>Direcci&oacute;n:
                <br>Ciudad:</span>
              </td>
            </tr>
            <tr class='print'>
              <td colspan='3' class='print'><br>Atentamente,</td>
            </tr>
            <tr class='print'>
              <td colspan='3' class='print'>
                <br>
                <span style='line-height:16px;' class='print'><b>ESTUDIANTE ENTREVISTADOR</b>
                <br><br><br>__________________________                
                <br>Nombre:
                <br>No. de identificaci&oacute;n:
                <br>Direcci&oacute;n:
                <br>Ciudad:</span>
              </td>
            </tr>
          </table>";
          break;
          default:
            $cadena = "";
            break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    /*case '73':// Fecha en que debe regresar el usuario
      $cadena = "";
      echo retornarCampoPersonalizado($cadena);
      break;*/
    case '83':// Documento firmado
      /*$cadena = "HECHOS (Se deben relacionar los hechos más relevantes del caso en orden cronológico con toda la información pertinente para el caso.)
      <br>La controversia que se desea solucionar tiene como hechos los siguientes:";*/
      $cadena = "Se deben relacionar los hechos más relevantes del caso en orden cronológico con todo la información pertinente para el caso.
      <br/>La controversia que desea solucionar tiene como hechos los siguiente:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '15':// Relato de los hechos (Claro, conciso y letra legible)
      $cadena = "El (La) usuario(a) solicita se consigne las siguientes pretensiones:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '16':// Pretensiones
      $cadena = "De igual manera se discriminan las pruebas que soporten los hechos relacionados:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '79':// Pruebas del solicitante
      switch($t_bug->project_id){
        case '21':
          $cadena = "De igual manera, la cuantía del conflicto asciende a la suma de:";
          break;
        default:
          $cadena = "De igual manera se discriminan las pruebas que soporten los hechos relacionados:";
          break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '80':// Pruebas citado
      $cadena = "De igual manera, la cuantía del conflicto asciende a la suma de:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '81':// Cuantía
      switch($t_bug->project_id){
        case '19':
          $cadena = "<center><b>ACUERDOS CONCILIATORIOS</b></center><br/>
          El acuerdo conciliatorio debe indicar la cuantía, modo, tiempo y lugar de cumplimiento de las obligaciones pactadas.<br/>
          <br/>
          Una vez propuestas las diferentes fórmulas de arreglo dentro de un ambiente de imparcialidad y legalidad, se llegó a un acuerdo respecto de las pretensiones solicitadas en los siguientes puntos:";
          break;
        case '17':
          $cadena = "Que a la audiencia de conciliación programada y debidamente notificada en la fecha &lt;&lt;Colocar Fecha&gt;&gt; por medio &lt;&lt;notificación personal o Correo certificado&gt;&gt; al señor (a) (a los señores): ";
          break;
        case '20':
          $cadena = "Que el asunto que se solicita no es susceptible de conciliar, desistir o transigir por las siguientes razones: ";
          break;
        case '18':
          $cadena = "<br/>
          <br/>
          ____________________________________<br/>
          FIRMA SOLICITANTE<br/>
          <br/>
          Nombre:  <br/> 
          Cédula N°:   <br/>
          Dirección:  <br/>
          Teléfono:   <br/>
          Celular:   <br/>
          <br/>
          <br/>
          ____________________________________
          <br/>
          FIRMA CITADO<br/>
          <br/>
          Nombre: <br/>  
          Cédula N°:  <br/> 
          Dirección:  <br/>
          Teléfono:   <br/>
          Celular:
          <br/>
          <br/>
          <br/>
          El (La) Conciliador a):
          <br/>
          <br/>
          <br/>
          ____________________________________
          <br/>
          FIRMA<br/>  
          Nombre:  <br/>
          Cédula N°  <br/>
          Tarjeta Profesional:   <br/>
          Estudiante asistente:
          <br/>
          <br/>
          <br/>
          ____________________________________
          <br/>
          FIRMA<br/>
          Nombre:   <br/>  
          Cédula:    <br/>
          Código:   <br/>
          ";
          break;
        default:
          $cadena = "Así mismo se relacionan los anexos de la solicitud de conciliación, los cuales dependen del caso en estudio:";
          break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '148': //Área
      $cadena = "<center><b>Datos del (de los) citado (s)</b></center>";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '88': //Tarjeta profesional
      $cadena = "<center>del consejo superior de la Judicatura (C.S.J.)</center>";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '90': //Fecha de la solicitud de conciliación
      switch($t_bug->project_id){
        case '19':
          $cadena = "<center><b>ACTA DE CONCILIACIÓN</b></center><br/>En la ciudad de Cali, el &lt;&lt;COLOCAR FECHA&gt;&gt;, siendo las  &lt;&lt;COLOCAR HORA&gt;&gt;, se reunieron en el Centro de Conciliación del Consultorio de la Facultad de Derecho y Ciencias Sociales de la Universidad Ices, el señor (a) (los señores):";
          break;
        case '17':
          $cadena = "<center><b>CONSTANCIA DE INASISTENCIA</b></center><br/>
          El conciliador del Centro de Conciliaci&oacute;n del Consultorio Jur&iacute;dico de la Facultad de Derecho y Ciencias Sociales de la Universidad Icesi, de conformidad con el art&iacute;culo 2 de la Ley 640 de 2001, deja constancia que:<br/>
          <center><b>Datos del (de los) solicitante(s)</b></center>";
          break;
        case '20':
          $cadena = "<center><b>CONSTANCIA DE ASUNTO NO CONCILIABLE</b></center><br/>
          El conciliador del Centro de Conciliaci&oacute;n del Consultorio Jur&iacute;dico de la Facultad de Derecho y Ciencias Sociales de la Universidad Icesi, de conformidad con el art&iacute;culo 2 de la Ley 640 de 2001, deja constancia que:<br/>
          <center><b>Datos del (de los) solicitante(s)</b></center>";
          break;
        default:
          $cadena = "";
          break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '169': //Estado civil (3)
      switch($t_bug->project_id){
        case '19':
          $cadena = "quien(es) solicitó (solicitaron) audiencia de conciliación con el señor  (a) (los señores):<br/>
          <center><b>Datos del (de los) citado (s)</b></center>";
          break;
        case '17':
          $cadena = " Solicitó(aron) conciliación en materia &lt;&lt;Seleccionar...LaboralCivil y Comercial o Familia&gt;&gt; para solucionar su conflicto referente a: &lt;&lt;COLOCAR CONFLICTO&gt;&gt; con el señor (los señores):<br/>
          <center><b>Datos del (de los) citado (s)</b></center>";
          break;
        case '20':
          $cadena = " Solicitó(aron) conciliación en materia &lt;&lt;Seleccionar...LaboralCivil y Comercial o Familia&gt;&gt; para solucionar su conflicto referente a: &lt;&lt;COLOCAR CONFLICTO&gt;&gt; con el señor (los señores):<br/>
          <center><b>Datos del (de los) citado (s)</b></center>";
          break;
        default:
          $cadena = "";
          break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '204': //Estado civil (cp)(6)
      switch($t_bug->project_id){
        case '19':
          $cadena = "Acto seguido, el conciliador(a) instala la audiencia de conciliación explicando el objeto, alcance y l&iacute;mites de la misma.<br/><br/>
          Se deben relacionar los hechos más relevantes del caso en orden cronológico con todo la información pertinente para el caso. La controversia que desea solucionar tiene como hechos los siguiente:";
          break;
        case '20':
          $cadena = "Que por proceder la solicitud anterior, se program&oacute; audiencia de conciliaci&oacute;n el d&iacute;a  &lt;&lt;COLOCAR FECHA&gt;&gt; a la hora &lt;&lt;COLOCAR LA HORA&gt;&gt; a realizarse en el Centro de Conciliaci&oacute;n del Consultorio Jur&iacute;dico de la Facultad de Derecho y Ciencias Sociales de la Universidad Icesi, Carrera 9 No. 9 -49 Segundo 2 Cali-Colombia.<br/><br/>
          Se deben relacionar los hechos más relevantes del caso en orden cronológico con todo la información pertinente para el caso. La controversia que desea solucionar tiene como hechos los siguiente:";
          break;
        default:
          $cadena = "";
          break;
      }
      echo retornarCampoPersonalizado($cadena);
      break;
    case '91': // Acuerdos conciliatorios
      $cadena = "Estando de acuerdo las partes sobre todo lo anterior por mutuo consentimiento, manifiestan que lo aceptan libremente y se  responsabiliza(n)   de  sus obligaciones y el (la) conciliador (a) &lt;&lt;COLOCAR NOMBRE DEL CONCILIADOR(A)&gt;&gt;  aprueba dichas fórmulas de arreglo y aclara nuevamente a las partes que el presente acuerdo hace tránsito a cosa juzgada, el acta de conciliación presta mérito ejecutivo y no es susceptible de ningún recurso.</br>
      </br>
      De ésta manera termina el desacuerdo que motivó la audiencia de conciliación y no siendo otro el objeto, en constancia de lo anterior se da por terminado siendo  las &lt;&lt;COLOCAR HORA&gt;&gt;  y se firma por quienes en ella intervinieron.<br/><br/>
<table width='100%' border='0' cellspacing='0' cellpadding='0'>
  <tr class='print'>
    <td colspan='3'><br />
    Solicitante(s):</td>
  </tr>
  <tr class='print'>
    <td class='print'><p>Firma:<br />
      <br />
      ________________________<br />
    Nombre:<br />
    N° de identificación:
    </p></td>
    <td class='print'>Firma:<br />
      <br />
________________________<br />
Nombre:<br />
N° de identificación: </td>
    <td class='print'>Firma:<br />
      <br />
________________________<br />
Nombre:<br />
N° de identificación: </td>
  </tr>
  <tr class='print'>
    <td colspan='3'><br />
    Citado(s):</td>
  </tr>
  <tr class='print'>
    <td class='print'>Firma:<br />
      <br />
      ________________________<br />
      Nombre:<br />
      N° de identificación: </td>
    <td class='print'>Firma:<br />
      <br />
      ________________________<br />
      Nombre:<br />
      N° de identificación: </td>
    <td class='print'>Firma:<br />
      <br />
      ________________________<br />
      Nombre:<br />
      N° de identificación: </td>
  </tr>
  <tr class='print'>
    <td class='print'>Firma:<br />
      <br />
________________________<br />
Nombre:<br />
N° de identificación: </td>
    <td class='print'>Firma:<br />
      <br />
________________________<br />
Nombre:<br />
N° de identificación: </td>
    <td class='print'>Firma:<br />
      <br />
________________________<br />
Nombre:<br />
N° de identificación: </td>
  </tr>
  <tr class='print'>
    <td colspan='3'><br />
    Apoderado:</td>
  </tr>
  <tr class='print'>
    <td class='print'>Firma:<br />
      <br />
________________________<br />
Nombre:<br />
N° de identificación: </td>
    <td class='print'>&nbsp;</td>
    <td class='print'>&nbsp;</td>
  </tr>
</table>";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '210': // Notificados
      $cadena = "asisti&oacute; &uacute;nicamente el se&ntilde;or(a) (los se&ntilde;ores):";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '211': // Calidad de *212 en DES - 211 en PRO
      $cadena = "Que pasados tres (3) días hábiles siguientes a la fecha en que debió celebrarse la audiencia de conciliación el incompareciente:<br/><br/>
____________________________________<br />
  FIRMA<br/><br/>
QUIEN COMPARECIÓ:<br/>
Nombre: <br />
  Cédula N° <br />
  Dirección: <br />
  Teléfono: <br />
  Celular: <br/><br/>
El (La) Conciliador a): <br />
  <br />
  ____________________________________<br />
  FIRMA <br />
  <br />
  Nombre: <br />
  Cédula N° <br />
  Tarjeta Profesional: <br />
  Estudiante asistente:<br />
  <br />
  ____________________________________<br />
  FIRMA<br />
  <br />
  Nombre: <br />
  Cédula: <br />
  Código:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '208': // Razones *209 en DES - 208 en PRO
      $cadena = "<br/>
      <br/>
      ____________________________________<br/>
FIRMA SOLICITANTE<br/>
<br/>
Nombre:  <br/> 
Cédula N°:   <br/>
Dirección:  <br/>
Teléfono:   <br/>
Celular:   <br/>
<br/>
<br/>
____________________________________<br/>
FIRMA CITADO<br/>
<br/>
Nombre: <br/>  
Cédula N°:  <br/> 
Dirección:  <br/>
Teléfono:   <br/>
Celular:<br/><br/><br/>
El (La) Conciliador a):  <br/>
<br/>
<br/>____________________________________<br/>
FIRMA<br/>  
Nombre:  <br/>
Cédula N°  <br/>
Tarjeta Profesional:   <br/>
Estudiante asistente:<br/>
<br/>
<br/>
____________________________________<br/>
FIRMA<br/>
Nombre:   <br/>  
Cédula:    <br/>
Código:   <br/>
";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '203':
      $cadena = "Se deben relacionar los hechos más relevantes del caso en orden cronológico con toda la información pertinente para el caso. La controversia que desea solucionar tiene como hechos los siguientes:";
      echo retornarCampoPersonalizado($cadena);
      break;
    case '209':
      $cadena = "asistió únicamente el señor(a) (los señores):";
      echo retornarCampoPersonalizado($cadena);
      break;
    default:
      break;
  }// END switch($t_custom_field_id)
  $cadena = "";
}// foreach
?>
</table>


<?php
echo '<br /><br />';
		} # end in_array
}  # end main loop
