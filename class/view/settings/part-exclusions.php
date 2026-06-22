<?php
namespace SPUI;
use \SPUI\Helper\UiHelper as UiHelper;
use SPUI\Helper\UtilHelper as UtilHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}


?>

<section id="tab-exclusions" class="<?php echo ($this->display_part == 'exclusions') ? 'active setting-tab' :'setting-tab'; ?>" data-part="exclusions" >

<settinglist>

  <h2><?php esc_html_e('Exclusions','shortpixel-upscale-image');?></h2>

  <!-- Exclude thumbnails -->
  <setting class='exclude-thumbnail-setting'>
     <name>
         <?php esc_html_e('Exclude thumbnail sizes','shortpixel-upscale-image');?>
                <i class='documentation up dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/how-can-i-upscale-only-certain-thumbnail-sizes/?target=iframe"></i>
     </name>
     <div class="grid-thumbnails">

       <?php
       foreach ( $view->allThumbSizes as $spui_size_key => $spui_size_val ) {
       ?>
           <span>
             <label>

               <?php
               $spui_exclude_sizes = property_exists($view->data, 'excludeSizes') ? $view->data->excludeSizes : array();
               $spui_checked = in_array( $spui_size_key, $spui_exclude_sizes, true ) ? 'checked' : '';
               $spui_width = isset($spui_size_val['width']) ? $spui_size_val['width'] : '*';
               $spui_height = isset($spui_size_val['height']) ? $spui_size_val['height'] : '*';

               $spui_name = isset($spui_size_val['nice-name']) ? $spui_size_val['nice-name'] : ucfirst($spui_size_key);
               $spui_label = $spui_name . " ( $spui_width &times $spui_height )";

               printf( ' <input name="excludeSizes[]" type="checkbox" id="excludeSizes_%1$s" value="%1$s" %2$s>%3$s', esc_attr( $spui_size_key ), checked( true, in_array( $spui_size_key, $spui_exclude_sizes, true ), false ), esc_html( $spui_label ) );
               ?>
               </label>
           </span>

       <?php } // exclude sizes ?>


     </div>
  </setting>
 <!-- // Exclude thumbnails -->

 <!-- Exclude patterns -->
 <setting class='exclude-patterns-setting'>
     <name>
       <?php esc_html_e('Exclude patterns','shortpixel-upscale-image');?>
       <label><input type='checkbox' class='shortpixel-hide' data-toggle='exclude-settings-expanded'> >> <?php esc_html_e( 'See examples', 'shortpixel-upscale-image' ); ?></label>

     </name>
     <info>
       <div class='exclude-settings-expanded toggleTarget ' id="exclude-settings-expanded">
         <p  class="settings-info">
         <?php
             /* translators: 1: Opening bold tag before "Name type:". 2: Closing bold tag. 3: Opening bold tag before "flower.jpg". 4: Closing bold tag. 5: Opening bold tag before "logo". 6: Closing bold tag. */
             printf( wp_kses_post( __( '%1$s"Name type:"%2$s Matches based on the file name only. For example, if you enter %3$s"flower.jpg"%4$s in the "Value" field, ShortPixel will exclude all JPEG images ending in "flower" (case-sensitive). Alternatively, you enter %5$s"logo"%6$s, all files (PNG/JPEG/GIF/PDF) containing "logo" in the file name will be excluded, such as: "nicelogo.jpg", "alllogos.png" or "logo.gif".', 'shortpixel-upscale-image' ) ),
             '<b>','</b>',
             '<b>','</b>',
             '<b>','</b>'
             );
         ?>

       </p>
       <br />
       <p  class="settings-info">
         <?php
             /* translators: 1: Opening bold tag before "Path type:". 2: Closing bold tag. 3: Opening bold tag before "2022". 4: Closing bold tag. 5: Opening bold tag before "/2022/". 6: Closing bold tag. */
             printf( wp_kses_post( __( '%1$s"Path type:"%2$s Matches based on the entire file path, which is useful for excluding specific directories or subdirectories. For instance, entering %3$s"2022"%4$s in the "Value" field will exclude all images uploaded in 2022, as well as any images with "2022" in the file name (since this is part of the path). To exclude only images uploaded in 2022, use %5$s"/2022/"%6$s instead.', 'shortpixel-upscale-image' ) ),
             '<b>','</b>',
             '<b>','</b>',
             '<b>','</b>'
             );
             ?>
           </p>
           <br />
           <p  class="settings-info">
         <?php
             /* translators: 1: Opening bold tag before "Name". 2: Closing bold tag. 3: Opening bold tag before "Path". 4: Closing bold tag. 5: Opening bold tag before "Check as regular expression". 6: Closing bold tag. 7: Opening bold tag before the regex example. 8: Closing bold tag. */
             printf( wp_kses_post( __( 'For both %1$s"Name"%2$s and %3$s"Path"%4$s types you can enable the %5$s"Check as regular expression"%6$s option. This works similarly but requires a valid regular expression between slashes in the "Value" field. Special characters should be escaped with a backslash (\). For instance, using %7$s/[0-9]+[^\/]*\.(PNG|png)/%8$s in the "Value" field for the "Name" type will exclude all PNG images with a numeric prefix.', 'shortpixel-upscale-image' ) ),
             '<b>','</b>',
             '<b>','</b>',
             '<b>','</b>',
             '<b>','</b>'
           );
           ?>
         </p>
         <br />
         <p  class="settings-info">
           <?php
             /* translators: 1: Opening bold tag before "Size type:". 2: Closing bold tag. 3: Opening bold tag before "Exact sizes". 4: Closing bold tag. */
             printf( wp_kses_post( __( '%1$s"Size type:"%2$s Applies to all images and thumbnails within the specified size range. You can set intervals or specify an exact size if the %3$s"Exact sizes"%4$s option is enabled.', 'shortpixel-upscale-image' ) ),
             '<b>','</b>',
             '<b>','</b>'
           );
           ?>
         </p>
      </div> <!-- foldout -->
     </info>
     <content>
         <info>
           <?php
           esc_html_e( 'Use this section to exclude images based on specific patterns. There are three exclusion types: by file name, file path or file size. Each exclusion type can be applied to: all images and their thumbnails (including scaled or original images), only thumbnails (in which case the original and scaled images are not excluded), only Custom Media images (Media Library items are not affected by this exclusion) or a specific selection of thumbnails. Examples can be found in the fold-out section below.', 'shortpixel-upscale-image' );
           ?>
         </info>



         <?php
         $spui_exclusion_format = "
            <li %s %s %s >
              <input type='hidden' name='exclusions[]' value='%s' />
							<span><b>%s </b><br> %s </span>
							<span><b>" . esc_html__('Apply to:', 'shortpixel-upscale-image') .  "</b><br> %s </span>
              <span class='regular_expression'><span class='regular-container %s'>" . esc_html__('Regular expression', 'shortpixel-upscale-image') . " %s</span>&nbsp;</span>
              <span> <i class='shortpixel-icon edit'></i>
              <i class='shortpixel-icon remove trash'></i> </span>
            </li>
         ";
         ?>

         <div id='exclusion-format' class='hidden'>

            <?php echo esc_textarea( $spui_exclusion_format ); ?>

         </div>

         <?php
          $spui_exclusions = UtilHelper::getExclusions();
             $spui_exclude_array = $spui_exclusions;
						 $spui_new_index = ( is_array( $spui_exclude_array ) && count( $spui_exclude_array ) > 0 ) ? ( count( $spui_exclude_array ) - 1 ) : 0;

                 echo "<ul class='exclude-list'>";
								 echo '<input type="hidden" id="new-exclusion-index" name="new-index" value="' . esc_attr( $spui_new_index ) . '">';
                 $spui_i = 0;

                 foreach ( $spui_exclude_array as $spui_index => $spui_option )
                 {
                     $spui_exclude_id  = 'id="exclude-' . $spui_i . '"';
                     $spui_type = (isset($spui_option['type'])) ? $spui_option['type'] : '';
										 $spui_value = isset($spui_option['value']) ? $spui_option['value'] : '';
										 $spui_apply = isset($spui_option['apply']) ? $spui_option['apply'] : '';
                     $spui_thumblist = isset($spui_option['thumblist']) ? $spui_option['thumblist'] : array();
                     $spui_has_error = (isset($spui_option['has-error']) && true == $spui_option['has-error']) ? true : false;

                     $spui_option_code = wp_json_encode($spui_option);

                     $spui_type_strings  = UiHelper::getSettingsStrings('exclusion_types');
                     $spui_apply_strings = UiHelper::getSettingsStrings('exclusion_apply');


                     $spui_apply_name = isset($spui_apply_strings[$spui_apply]) ? $spui_apply_strings[$spui_apply] : '';

                     switch($spui_type)
                     {
                        case 'name':
                        case 'regex-name':
                          $spui_field_name = $spui_type_strings['name'];
                        break;
                        case 'path':
                        case 'regex-path':
                         $spui_field_name = $spui_type_strings['path'];
                        break;
                        case 'size':
                          $spui_field_name = $spui_type_strings['size'];
                        break;
                        case 'filesize': 
                          $spui_field_name = $spui_type_strings['filesize'];
                        break; 
                        case 'date': 
                          $spui_field_name = $spui_type_strings['date'];
                        break; 
                        default:
                          $spui_field_name = __('Unknown', 'shortpixel-upscale-image');
                        break;
                     }


                     $spui_classes = array();
                     if ( true === $spui_has_error )
                     {
                        $spui_classes[] = 'has-error';
                     }

                     if ( strpos( $spui_type, 'regex' ) !== false )
                     {
                         $spui_classes[] = 'is-regex';
                     }

                     $spui_class = '';
                     if ( count( $spui_classes ) > 0 )
                     {
                        $spui_class = 'class="' . implode( ' ', $spui_classes ) . '"';
                     }


                     $spui_title = '';
                     if ( 'selected-thumbs' == $spui_apply )
                     {
                        $spui_thumb_titles = array();
                        foreach ( $spui_thumblist as $spui_thumb_name )
                        {
                           $spui_thumb = $view->allThumbSizes[$spui_thumb_name];
                           $spui_thumb_titles[] = (isset($spui_thumb['nice-name'])) ? $spui_thumb['nice-name'] : $spui_thumb_name;
                        }
                        $spui_title = 'title="' . esc_attr( implode( ', ', $spui_thumb_titles ) ) . '"';
                     }

                     printf(
                        wp_kses_post( $spui_exclusion_format ),
                        esc_attr( $spui_class ),
                        esc_attr( $spui_title ),
                        esc_attr( $spui_exclude_id ),
                        esc_attr( $spui_option_code ),
                        esc_html( $spui_field_name ),
                        esc_html( $spui_value ),
                        esc_html( $spui_apply_name ),
                        '',
                        ''
                     );

                     $spui_i++;
                 }
                 echo "</ul>";

         ?>
                     <div class='new-exclusion not-visible'>
                         <!-- HEADER -->
                         <input type="hidden" name="edit-exclusion" value="">
                         <h3 class='new-title not-visible'><?php esc_html_e('Add New Exclusion' ,'shortpixel-upscale-image'); ?></h3>
                         <h3 class='edit-title not-visible'><?php esc_html_e('Edit Exclusion' ,'shortpixel-upscale-image'); ?></h3>

                         <div>
                           <label><?php esc_html_e('Type:', 'shortpixel-upscale-image'); ?></label>
                            <select name="exclusion-type" class='new-exclusion-type'>
                               <option value='name'><?php esc_html_e('Image Name', 'shortpixel-upscale-image'); ?></option>
                               <option value='path' data-example="/path/"><?php esc_html_e('Image Path', 'shortpixel-upscale-image'); ?></option>
                               <option value='size' data-example="widthXheight-widthXheight"><?php esc_html_e('Image Size', 'shortpixel-upscale-image'); ?></option>
                               <option value='filesize' data-example="500KB / 1MB"><?php esc_html_e('Image Filesize', 'shortpixel-upscale-image'); ?></option>
                               <option value='date' data-example="YYYY-MM-DD"><?php esc_html_e('Date', 'shortpixel-upscale-image') ?></option> 
                           </select>
                         </div>

                             <div class='value-option '>
                               <label><?php esc_html_e('Value:', 'shortpixel-upscale-image'); ?></label>
                               <input type="text" name="exclusion-value" value="">
                         </div>

                             <div class='size-option not-visible'>
                                 <div class='exact-option'>
                                   <label>&nbsp;</label>
                                   <div class='switch_button'>
                                     <label>
                                       <input type="checkbox" class="switch" name="exclusion-exactsize">
                                       <div class="the_switch">&nbsp; </div>
                                       <?php esc_html_e('Exact sizes','shortpixel-upscale-image');?>
                                     </label>
                                   </div>
                                 </div>

                                 <div class='size-option-range'>
                                   <div class='width'>
                                       <label><?php esc_html_e('Width between:', 'shortpixel-upscale-image'); ?></label>
                                       <input type="number" class='small' name="exclusion-minwidth" value="" min="0">px -
                                       <input type="number" class='small' name="exclusion-maxwidth" value="" min="0">px
                                   </div>
                                   <div class='height'>
                                       <label><?php esc_html_e('Height between:', 'shortpixel-upscale-image'); ?></label>
                                       <input type="number" class='small' name="exclusion-minheight" value="" min="0">px -
                                       <input type="number" class='small' name="exclusion-maxheight" value="" min="0">px
                                   </div>
                                 </div>

                                 <div class='size-option-exact not-visible'>
                                   <div class='exact'>
                                     <label>
                                       <?php esc_html_e('Exact size:', 'shortpixel-upscale-image'); ?></label>
                                       <input type="number" class='small' name="exclusion-width" value="" min="0">px -
                                       <input type="number" class='small' name="exclusion-height" value="" min="0">px
                                    </div>
                                 </div>
                             </div>

                        <div class='date-option not-visible'>
                        <label><?php esc_html_e('Date Options:', 'shortpixel-upscale-image'); ?></label>
                              <select name='exclusion-when'>
                                <option value='before'><?php esc_html_e('Before this date', 'shortpixel-upscale-image'); ?></option>
                                <option value='after'><?php esc_html_e('After this date', 'shortpixel-upscale-image'); ?></option>
                              </select>
                            
                        </div>
                        <div class='operator-option not-visible'>
                          <label>&nbsp;</label>
                          <input type="number" name="exclusion-filesize-value" value='' class='small'>
                          <select name="exclusion-filesize-denom">
                            <option value="B"><?php esc_html_e('Bytes', 'shortpixel-upscale-image'); ?></option>
                            <option value="K" selected><?php esc_html_e('Kilobytes (KB)', 'shortpixel-upscale-image'); ?></option>
                            <option value="M"><?php esc_html_e('Megabytes (MB)', 'shortpixel-upscale-image'); ?></option>
                          </select>
                          <select name='exclusion-filesize-operator'>
                              <option value='<'><?php esc_html_e('Exclude smaller than given filesize', 'shortpixel-upscale-image'); ?></option>
                              <option value='>' selected><?php esc_html_e('Exclude higher than given filesize', 'shortpixel-upscale-image'); ?></option>
                          </select>

                        </div>

                         <div class='applyto-option' >
                           <label><?php esc_html_e('Apply To:', 'shortpixel-upscale-image'); ?></label>
                           <select name='apply-select' class='thumbnail-type-option'>
                               <option value='all'><?php esc_html_e('All Images', 'shortpixel-upscale-image'); ?></option>
                               <option value='only-thumbs'><?php esc_html_e('Only Thumbnails','shortpixel-upscale-image'); ?>
                               </option>
                               <option value='only-custom'><?php esc_html_e('Only Custom Media images', 'shortpixel-upscale-image'); ?>
                               </option>
                               <option value='selected-thumbs'><?php esc_html_e('Selected thumbnails', 'shortpixel-upscale-image'); ?></option>
                           </select>
                         </div>

                        <div class='regex-option'>
                          <label>&nbsp;</label>
                          <div class='switch_button'>
                            <label>
                              <input type="checkbox" class="switch" name="exclusion-regex">
                              <div class="the_switch">&nbsp; </div>
                              <?php esc_html_e('Check as regular expression','shortpixel-upscale-image');?>
                            </label>
                          </div>
                        </div>


                         <div class='thumbnail-select'>
                           <h4><?php esc_html_e('Selected Thumbnails', 'shortpixel-upscale-image'); ?><hr></h4>
                           <div class='grid-thumbnails'>
                               <?php foreach ( $view->allThumbSizes as $spui_key => $spui_data )
                               {
                                   $spui_width = isset($spui_data['width']) ? $spui_data['width'] : '*';
                                   $spui_height = isset($spui_data['height']) ? $spui_data['height'] : '*';

                                   $spui_name = isset($spui_data['nice-name']) ? $spui_data['nice-name'] : ucfirst($spui_key);
                                   $spui_label = $spui_name . " ( $spui_width &times $spui_height )";

                                printf( '<span><label><input type="checkbox" name="thumbnail-select[]" value="%s" > %s </label></span>', esc_attr( $spui_key ), esc_html( $spui_label ) );
                               } ?>
                          </div>
                         </div>
                         <div class='validation-message not-visible'>
                            <?php esc_html_e('Fields with a red border are required', 'shortpixel-upscale-image'); ?>
                         </div>

                         <div class='button-actions'>

                           <button type="button" class="button button-primary not-visible" name="addExclusion">
                             <i class="shortpixel-icon save"> </i>
                             <?php esc_html_e('Save', 'shortpixel-upscale-image'); ?>
			   </button>

                           <button type="button" class="button button-primary not-visible" name="updateExclusion">
			     <i class="shortpixel-icon save"> </i>
                             <?php esc_html_e('Update', 'shortpixel-upscale-image');  ?>
                           </button>

                           <button type="button" class="button" name='cancelEditExclusion'>
			     <i class="shortpixel-icon close"> </i>
			     <?php esc_html_e('Close', 'shortpixel-upscale-image'); ?>
			   </button>

                           <button type="button" class="button button-primary not-visible" name="removeExclusion">
			     <i class="shortpixel-icon close"> </i>
                             <?php esc_html_e('Remove', 'shortpixel-upscale-image');  ?>
                           </button>

                         </div>
                       </div> <!-- new exclusion -->

                       <button class='button button-primary new-exclusion-button' type='button' name="addNewExclusion">
                         <?php esc_html_e('Add new Exclusion', 'shortpixel-upscale-image'); ?>
                       </button>

             <info class='exclusion-save-reminder hidden'><?php esc_html_e('Reminder: Save the settings for the   exclusion changes to take effect!', 'shortpixel-upscale-image'); ?></info>
     </content>
 </setting>
 <!-- // Exclude patterns -->

</settinglist>


  <?php $this->loadView('settings/part-savebuttons', false); ?>
</section>
