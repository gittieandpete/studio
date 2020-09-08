<?php

// Ein Textfeld ausgeben
function input_text_sklar($elementname, $werte) { ?> 
    <input type='text' name='<?php print $elementname;?>' value='<?php print htmlentities($werte[$elementname]);?>'>
<?php }

// Einen Absenden-Button ausgeben
function input_submit_sklar($elementname, $label) { ?> 
    <input type='submit' name='<?php print $elementname;?>' value='<?php print htmlentities($label);?>'>
<?php }

// Ein mehrzeiliges Textfeld ausgeben
function input_textarea_sklar($elementname, $werte) { ?> 
    <textarea name='<?php print $elementname;?>'><?php print htmlentities($werte[$elementname]);?></textarea>
<?php }

// Einen Radiobutton oder eine Checkbox ausgeben
function input_radiocheck_sklar($typ, $elementname, $werte, $elementwert) { ?> 
    <input type='<?php print $typ;?>' name='<?php print $elementname;?>' value='<?php print $elementwert;?>'
    <?php if ($elementwert == $werte[$elementname]) { ?>
        checked='checked'
    <?php }
    // schlie�endes tag input ?>
    >
<?php }

// Ein <select>-Men� ausgeben
function input_select_sklar($elementname, $ausgewaehlt, $optionen, $multiple = false) {
    // Das <select>-Tag ausgeben
    print '<select name="' . $elementname;
    // Wenn mehrere Auswahlen m�glich sind, das multiple-Attribut
    // hinzuf�gen und an das Ende des Tag-Namens ein [  ] anh�ngen
    if ($multiple) { print '[  ]" multiple="multiple'; }
    print '">';

    // Die Liste der ausw�hlbaren Dinge einrichten
    $ausgewaehlte_optionen = array(  );
    if ($multiple) {
        foreach ($ausgewaehlt[$elementname] as $wert) {
            $ausgewaelte_optionen[$wert] = true;
        }
    } else {
        $ausgewaelte_optionen[ $ausgewaehlt[$elementname] ] = true;
    }

    // Die <option>-Tags ausgeben
    foreach ($optionen as $option => $label) {
        print '<option value="' . htmlentities($option) . '"';
        if (isset($ausgewaelte_optionen[$option])) {
            print ' selected="selected"';
        }
        print '>' . htmlentities($label) . '</option>';
    }
    print '</select>';
}

?>