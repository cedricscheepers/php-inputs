<?php
	class Inputs {
		protected $language;
		protected $arr = array();
		protected $parameters;
		protected $shared;
		
		public function __construct(&$language, $parameters = null, $shared = null) {
			$this -> language = $language;
			$this -> parameters = isset($parameters) ? $parameters : null;
			$this -> shared = isset($shared) ? str_replace('||', '|', $shared.'|') : null;
			
			$this -> build();
		}
		
		public function field($field) {
			return (isset($this -> arr[strtolower($field)]) ? $this -> arr[strtolower($field)] : null);
		}
		
		public function addParameters($parameters) {
			$this -> parameters = array_merge($this -> parameters, $parameters);

			$this -> build();
		}
		
		private function build() {
			if (!isset($this -> parameters)) {
				exit;
			}
			
			// Looping through the rules
			foreach ($this -> parameters as $fields => $values) {
				
				$rules = explode('|', (isset($this -> shared) ? $this -> shared : '').$values);
				$rules = array_filter($rules);					// Remove empty items
				$size = sizeof($rules);

				// Setting the variables to null
				$variables = array('group' => true);

				for ($counter = 0; $counter < $size; ++$counter) {
					$tmp = explode(',', $rules[$counter], 2);
					$rule = $tmp[0];

					array_shift($tmp);
					
					$parameter = isset($tmp[0]) ? implode(',', $tmp) : null;
					$rule = strtolower($rule);
					
					switch ($rule) {
						case 'checkbox':
						case 'color':
						case 'hidden':
						case 'number':
						case 'radio':
						case 'search':
						case 'tel':
						case 'text':
						case 'textarea':
						case 'url':
						case 'date':
						case 'time':
							$variables['type'] = $rule;
							
							break;
						
						case 'email':
							$variables['type'] = 'email';
							$variables['placeholder'] = $this -> language['email address'];
							$variables['maxlength'] = 50;
							$variables['autocomplete'] = false;
							
							break;
							
						case 'password':
							$variables['type'] = 'password';
							$variables['placeholder'] = $this -> language['password'];
							$variables['maxlength'] = 30;
							$variables['autocomplete'] = false;
							$variables['pattern'] = '(?=^.{6,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$';
							
							break;
							
						case 'name':
							if (!isset($parameter)) { 
								$variables['type'] = 'text';
								$variables['placeholder'] = $this -> language['name'];
								$variables['autocomplete'] = false;
								$variables['spellcheck'] = false;
								$variables['maxlength'] = 40;
							} else {
								$variables['name'] = $parameter;
							}
							
							break;
							
						case 'firstname':
							$variables['type'] = 'text';
							$variables['placeholder'] = $this -> language['first name'];
							$variables['autocomplete'] = false;
							$variables['spellcheck'] = false;
							$variables['maxlength'] = 40;
							
							break;
							
						case 'lastname':
							$variables['type'] = 'text';
							$variables['placeholder'] = $this -> language['last name'];
							$variables['autocomplete'] = false;
							$variables['spellcheck'] = false;
							$variables['maxlength'] = 40;
							
							break;
							
						case 'file':
						case 'rating':
						case 'select':
						case 'switch':
							$variables['type'] = $rule;
							$variables['group'] = false;
							
							break;
							
						case 'ids':
							$variables['id'] = $parameter;
							$variables['name'] = $parameter;
							
							break;

						case 'checked':
							$variables['checked'] = $parameter === true || (int)$parameter === 1 || in_array(strtolower($parameter), array('on', 'true', 'yes'));
							
							if ($variables['checked'] === false) {
								unset($variables['checked']);
							}
							
							break;

						case 'value':							
							
							if (!in_array($variables['type'], array('date', 'switch', 'textarea', 'time'))) {
								$variables['value'] = $parameter;
							} elseif ($variables['type'] === 'switch') {
								$variables['checked'] = $parameter === true || (int)$parameter === 1 || in_array(strtolower($parameter), array('on', 'true', 'yes'));
							} elseif ($variables['type'] === 'date') {
								$variables['value'] = isset($parameter) && $parameter !== '' ? date_parse_from_format('Y-m-d', $parameter) : null;
							} elseif ($variables['type'] === 'time') {
								$variables['value'] = isset($parameter) && $parameter !== '' ? explode(':', date('H:i', strtotime($parameter))) : null;
							} else {
								$variables['value'] = isset($parameter) && $parameter !== '' ? str_replace('<br />', "\n", $parameter) : null;
							}
							
							break;

						case 'focus':
						case 'required':
							$variables[$rule] = true;
							
							break;

						case 'min':
						case 'max':
						case 'rows':
						case 'step':
							$variables[$rule] = $variables['type'] !== 'date' ? (int)$parameter : $parameter;
							
							break;
							
						case 'maxlength':
						case 'max_length':
						case 'max_len':
							$variables['maxlength'] = (int)$parameter;
							
							break;

						case 'autocomplete':
						case 'group':
						case 'spellcheck':
							$variables[$rule] = $parameter === true || (int)$parameter === 1;
							
							break;

						case 'on':
						case 'off':
							$variables[$rule] = ucfirst($parameter);
							
							break;
							
						case 'td':
							$variables['td'] = isset($parameter) && (int)$parameter > 0 ? (int)$parameter : 1;
							
							break;

						case 'multiple':
							$variables['multiple'] = !isset($parameter) || $parameter === true || (int)$parameter === 1;
							
							break;

						case 'ratio':
							$variables['ratio'] = (int)$parameter == $parameter ? (int)$parameter : (float)$parameter;
							
							break;
							
						default:	// action, accesskey, align, autofill, class, help, id, list, onchange, onclick, options, placeholder, unique
							$variables[$rule] = $parameter;
					}
				}
				
				// Build the html
				
				if ($variables['type'] !== 'hidden') {
					$out = (isset($variables['td']) ? '<td'.($variables['td'] > 1 ? ' colspan='.$variables['td'] : '').
							(isset($variables['align']) ? ' class="'.$variables['align'].'"' : '').'>' : '').
							($variables['group'] ? '<div class="group">' : '');
				} else {
					$out = '';
				}

				switch ($variables['type']) {
					case 'checkbox':
					case 'radio':
						$out .= '<input type="'.$variables['type'].'"'.
								(isset($variables['id']) ? ' id="'.$variables['id'].'"' : '').
								(isset($variables['name']) ? ' name="'.$variables['name'].'"' : '').
								(isset($variables['class']) ? ' class="'.$variables['class'].'"' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['checked']) ? ' checked' : '').
								(isset($variables['required']) ? ' required' : '').
								(isset($variables['value']) ? ' value='.(is_numeric($variables['value']) ? $variables['value'] : '"'.$variables['value'].'"') : '').'>'.
								(isset($variables['label']) ? ' <label for="'.$variables['id'].'">'.$variables['label'].'</label>' : '');
	
						break;
						
					case 'date':
						$out .= '<select id="'.$variables['id'].'Month" name="'.$variables['name'].'Month"'.(isset($variables['required']) ? ' required' : '').'>';
						
						if (!isset($variables['required']) || isset($variables['placeholder'])) {
							$out .= '<option value=0 '.(!isset($variables['value']) ? 'selected' : '').(!isset($variables['required']) ? '' : ' disabled').'>'.(isset($variables['placeholder']) ? $variables['placeholder'] : 'Month').'</option>';
						}

						for ($counter = 1; $counter <= 12; ++$counter) {
							$out .= '<option value='.$counter.(isset($variables['value']) && $counter === $variables['value']['month'] ? ' selected' : '').'>'.
									date('M', mktime(0, 0, 0, $counter, 10)).'</option>';
						}
						
						$out .= '</select><select class="date" id="'.$variables['id'].'Day" name="'.$variables['name'].'Day"'.(isset($variables['required']) ? ' required' : '').'>';
		
						if (!isset($variables['required']) || isset($variables['placeholder'])) {
							$out .= '<option value=0 '.(!isset($variables['value']) ? 'selected' : '').(!isset($variables['required']) ? '' : ' disabled').'>'.(isset($variables['placeholder']) ? ' ' : 'Day').'</option>';
						}

						for ($counter = 1; $counter <= 31; ++$counter) {
							$out .= '<option value='.$counter.(isset($variables['value']) && $counter === $variables['value']['day'] ? ' selected' : '').'>'.
									$counter.'</option>';
						}
						
						$out .= '</select><select class="date" id="'.$variables['id'].'Year" name="'.$variables['name'].'Year"'.(isset($variables['required']) ? ' required' : '').'>';
						
						if (!isset($variables['required']) || isset($variables['placeholder'])) {
							$out .= '<option value=0 '.(!isset($variables['value']) ? 'selected' : '').(!isset($variables['required']) ? '' : ' disabled').'>'.(isset($variables['placeholder']) ? ' ' : 'Year').'</option>';
						}
		
						// Calculating range
						
						$start = isset($variables['min']) ? (is_numeric($variables['min']) ? (int)$variables['min'] : date('Y', $variables['min'])) : date('Y') - 1;
						$end = isset($variables['max']) ? (is_numeric($variables['max']) ? (int)$variables['max'] : date('Y', $variables['max'])) : date('Y') + 1;

						// If start is larger than max, then swop
						if ($start > $end) {
							 $start ^= $end ^= $start ^= $end;
						}
												
						for ($counter = $start; $counter <= $end; ++$counter) {
							$out .= '<option value='.$counter.(isset($variables['value']) && $counter === $variables['value']['year'] ? ' selected' : '').'>'.
									$counter.'</option>';
						}
						
						$out .= '</select>';
						
						break;
					
					case 'file':
						$out .= '<label class="btn">
								<input type="file"'.
								(isset($variables['id']) ? ' id="'.$variables['id'].'"' : '').
								(isset($variables['name']) ? ' name="'.$variables['name'] : '').
								(isset($variables['multiple']) && $variables['multiple'] ? '[]" multiple="multiple"' : '"').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['ratio']) ? ' data-ratio="'.$variables['ratio'].'"' : '').
								(isset($variables['required']) ? ' required' : '').' />'.
								(isset($variables['placeholder']) ? $variables['placeholder'] : 'Upload a File').'</label>';
						
						break;
	
					case 'hidden':
						$out .= '<input type="hidden"'.
								(isset($variables['id']) ? ' id="'.$variables['id'].'"' : '"').
								(isset($variables['name']) ? ' name="'.$variables['name'].'"' : '"').' 
								value="'.(isset($variables['value']) ? $variables['value'] : '').'"'.
								(isset($variables['required']) ? ' required' : '').'>';
	
						break;

					case 'rating':
						$id = isset($variables['id']) ? $variables['id'] : $variables['name'];
						
						$out .= '<input type="radio" id="'.$id.'5" name="'.$variables['name'].'" value=5'.
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['value']) && (int)$variables['value'] === 5 ? ' checked' : '').'>
								<label for="'.$id.'5"> </label>
								<input type="radio" id="'.$id.'4" name="'.$variables['name'].'" value=4'.
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['value']) && (int)$variables['value'] === 4 ? ' checked' : '').'>
								<label for="'.$id.'4"> </label>
								<input type="radio" id="'.$id.'3" name="'.$variables['name'].'" value=3'.
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['value']) && (int)$variables['value'] === 3 ? ' checked' : '').'>
								<label for="'.$id.'3"> </label>
								<input type="radio" id="'.$id.'2" name="'.$variables['name'].'" value=2'.
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['value']) && (int)$variables['value'] === 2 ? ' checked' : '').'>
								<label for="'.$id.'2"> </label>
								<input type="radio" id="'.$id.'1" name="'.$variables['name'].'" value=1'.
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['value']) && (int)$variables['value'] === 1 ? ' checked' : '').
								(isset($variables['required']) ? ' required' : '').'>
								<label for="'.$id.'1"> </label>';
					
						break;
	
					case 'select':
						$out .= '<select id="'.$variables['id'].'" name="'.$variables['name'].'"'.
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['multiple']) && $variables['multiple'] ? ' multiple' : '').'>'.
								(isset($variables['placeholder']) ? '<option id=0 selected>'.$variables['placeholder'].'</option>' : '');
												
						$json = json_decode($variables['options'], true);
						$rows = sizeof($json);
						$numeric = isset($variables['value']) ? is_numeric($variables['value']) : false;
						
						for ($counter = 0; $counter < $rows; ++$counter) {
							$out .= '<option value='.($numeric ? $json[$counter]['id'] : '"'.$json[$counter]['id'].'"').
									(isset($variables['value']) && ($numeric ? (int)$variables['value'] === (int)$json[$counter]['id'] : $variables['value'] === $json[$counter]['id']) ? ' selected' : '').'>'.
									$json[$counter]['name'].'</option>';
						}
						
						$out .= '</select>';
	
						break;
						
					case 'switch':
						$out .= '<label class="switch" for="'.$variables['id'].'">
								<input type="checkbox" class="switch" id="'.$variables['id'].'" name="'.$variables['id'].'"'.
								(isset($variables['checked']) && $variables['checked'] ? ' checked' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['onclick']) ? ' data-click=\''.$variables['onclick'].'\'' : '').
								(isset($variables['action']) ? ' '.$variables['action'] : '').'>
								<div class="switch-inner" data-on="'.$variables['on'].'" data-off="'.$variables['off'].'">
								<div class="switch-switch"></div></div></label>';
					
						break;
						
					case 'time':
						$out .= '<select id="'.$variables['id'].'Hour" name="'.$variables['name'].'Hour">';
						
						for ($counter = 0; $counter <= 23; ++$counter) {
							$out .= '<option value='.$counter.(isset($variables['value']) && (int)$variables['value'][0] !== $counter ? '' : ' selected').'>'.
									($counter < 10 ? '0'.$counter : $counter).'</option>';
						}
						
						$out .= '</select><select class="date" id="'.$variables['id'].'Min" name="'.$variables['name'].'Min">
								<option value=0'.(isset($variables['value']) && (int)$variables['value'][1] !== 0 ? '' : ' selected').'>00</option>
								<option value=15'.(isset($variables['value']) && (int)$variables['value'][1] !== 15 ? '' : ' selected').'>15</option>
								<option value=30'.(isset($variables['value']) && (int)$variables['value'][1] !== 30 ? '' : ' selected').'>30</option>
								<option value=45'.(isset($variables['value']) && (int)$variables['value'][1] !== 45 ? '' : ' selected').'>45</option></select>';
						
						break;
					
					case 'textarea':
						$out .= '<textarea'.
								(isset($variables['id']) ? ' id="'.$variables['id'].'"' : '').
								(isset($variables['name']) ? ' name="'.$variables['name'].'"' : '').
								(isset($variables['class']) ? ' class="'.$variables['class'].'"' : '').
								(isset($variables['placeholder']) ? ' placeholder="'.$variables['placeholder'].'"' : '').
								(isset($variables['autofill']) ? ' data-autofill=\''.$variables['autofill'].'\'' : '').
								(isset($variables['focus']) ? ' autofocus' : '').
								(isset($variables['spellcheck']) ? ' spellcheck="'.($variables['spellcheck'] ? 'true' : 'false').'"' : '').
								(isset($variables['pattern']) ? ' pattern="'.$variables['pattern'].'"' : '').
								(isset($variables['help']) ? ' data-help=\''.$variables['help'].'\'' : '').
								(isset($variables['rows']) ? ' rows='.$variables['rows'] : '').
								(isset($variables['accesskey']) ? ' accesskey='.$variables['accesskey'] : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['action']) ? ' '.$variables['action'] : '').
								(isset($variables['required']) ? ' required' : '').'>'.
								(isset($variables['value']) ? $variables['value'] : '').'</textarea>';
	
						break;
						
					default:
						$out .= '<input type="'.$variables['type'].'"'.
								(isset($variables['id']) ? ' id="'.$variables['id'].'"' : '').
								(isset($variables['name']) ? ' name="'.$variables['name'].'"' : '').
								(isset($variables['class']) ? ' class="'.$variables['class'].'"' : '').
								(isset($variables['placeholder']) ? ' placeholder="'.$variables['placeholder'].'"' : '').
								(isset($variables['autofill']) ? ' data-autofill=\''.$variables['autofill'].'\'' : '').
								(isset($variables['focus']) ? ' autofocus' : '').
								(isset($variables['value']) ? ' value="'.$variables['value'].'"' : '').
								(isset($variables['min']) ? ' min='.$variables['min'] : '').
								(isset($variables['step']) ? ' step='.$variables['step'] : '').
								(isset($variables['max']) ? ' max='.$variables['max'] : '').
								(isset($variables['maxlength']) ? ' maxlength='.$variables['maxlength'] : '').
								(isset($variables['spellcheck']) ? ' spellcheck="'.($variables['spellcheck'] ? 'true' : 'false').'"' : '').
								(isset($variables['list']) ? ' data-list=\''.$variables['list'].'\'' : (isset($variables['pattern']) ? ' pattern="'.$variables['pattern'].'"' : '')).
								(isset($variables['help']) ? ' data-help=\''.$variables['help'].'\'' : '').
								(isset($variables['accesskey']) ? ' accesskey='.$variables['accesskey'] : '').
								(isset($variables['autocomplete']) ? ' autocomplete="'.($variables['autocomplete'] ? 'on' : 'off').'"' : '').
								(isset($variables['onchange']) ? ' data-change=\''.$variables['onchange'].'\'' : '').
								(isset($variables['unique']) ? ' data-unique=\''.$variables['unique'].'\'' : '').
								(isset($variables['action']) ? ' '.$variables['action'] : '').
								(isset($variables['required']) ? ' required' : '').'>';
				}
				
				$out .= ($variables['type'] !== 'hidden' && $variables['group'] ? (!in_array($variables['type'], array('date', 'time')) ? '<i class="group-addon fa fa- fa-fw"></i>' : '').'</div>' : '').
						($variables['type'] !== 'hidden' && isset($variables['td']) ? '</td>' : '');

				// Add to data array (and rename if required)
				$this -> arr[strtolower($fields)] = $out;
			}
		}
	}
?>