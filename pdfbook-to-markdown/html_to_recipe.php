<?php

function html_to_obj($html) {
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    return element_to_obj($dom->documentElement);
}
function element_to_obj($element) {
    $obj = array( "tag" => $element->tagName );
    foreach ($element->attributes as $attribute) {
        $obj[$attribute->name] = $attribute->value;
    }
    foreach ($element->childNodes as $subElement) {
        if ($subElement->nodeType == XML_TEXT_NODE) {
            $obj["html"] = $subElement->wholeText;
        }
        else {
            $obj["children"][] = element_to_obj($subElement);
        }
    }
    return $obj;
}

function html_to_recipe_arr($html) {
    $obj = html_to_obj($html);
    if (!isset($obj['children'][1]['children'][0]['children'])) {
        return null;
    }
    $doc = $obj['children'][1]['children'][0]['children'];
    
    $valid_word = false;
    $area_index = $part_index = $line_index = $word_index = 0;
    $page = [];
    foreach ($doc as $area) {
        $is_numeric_count = 0;
        foreach($area['children'] as $part) {
            foreach($part['children'] as $line) {
                foreach($line['children'] as $word) {
                    if ($word['html'] === 'GHQs—' || $word['html']=== 'CxOo—' || $word['html']==='CoOr—' || $word['html']==='GeQs—') {
                        $valid_word = false;
                        break;
                    } else {
                        $page['areas'][$area_index]['parts'][$part_index]['lines'][$line_index]['words'][$word_index] = $word['html'];
                        $word_index++;
                        $valid_word = true;
                    }
                }
                if ($valid_word) {
                    if (is_numeric($line['children'][0]['html'][0])) {
                        $is_numeric_count++;
                    }
                    $line_index++;
                }
            }
            if ($valid_word) {
                $part_index++;
            }
        }
        if ($valid_word) {
            if ($is_numeric_count > (ceil(count($area['children'][0]['children'])/2))) {
                $page['areas'][$area_index]['meta'] = 'ingredients';
            }
            if ($area_index === 0) {
                $page['areas'][$area_index]['meta'] = 'title';
            }
            $area_index++;
        }
    }
    $keys = array_keys($page['areas']);
    $last_index = end($keys);
    $page['areas'][$last_index]['meta'] = 'pagenumber';
    // var_dump($page);
    
    
    $recipe = ['title'=>'','body'=>'','pagenumber'=>null];
    foreach($page['areas'] as $area) {
        foreach ($area['parts'] as $part) {
            foreach($part['lines'] as $line) {
                if (isset($area['meta'])) {
                    if ($area['meta'] == 'title') {
                        $recipe['title'] .= implode(' ',$line['words']).' ';
                    } else if ($area['meta'] == 'ingredients') {
                        $recipe['body'] .= '- '.implode(' ',$line['words']);
                    } else if ($area['meta'] == 'pagenumber' && is_numeric(trim(implode(' ',$line['words'])))) {
                        $recipe['pagenumber'] .= trim(implode(' ',$line['words']));
                    }
                } else {
                    $recipe['body'] .= implode(' ',$line['words'])." ";            
                }
            }
            $recipe['body'] .= "\n";
        }
        $recipe['body'] .= "\n";
    }
    $recipe['body'] = trim($recipe['body']);
    $recipe['body'] = str_replace('|','1',$recipe['body']);
    $recipe['ingredients'] = [];
    $recipe['categories'] = [];
    return $recipe;
}


