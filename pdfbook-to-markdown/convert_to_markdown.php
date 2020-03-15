<?php
include_once('html_to_recipe.php');

$ingredients = [
    "Apples","Bananas","Beef","Chicken","Chocolate",
    "Crab","Cream Cheese","Egg","Lemon","Pasta","Peaches",
    "Pork","Potatoes","Shrimp","Strawberries","Turkey"
];
$categories = [
    'Breakfasts & Brunch'=>[1,30],
    'Appetizers & Snacks'=>[32,58],
    'Soups & Stews'=>[59,80],
    'Instant Pot (Pressure Cooker) Cooking'=>[81,92],
    'Main Dishes'=>[93,151],
    'Side Dishes & Sauces'=>[152,176],
    'Camping Food'=>[177,195],
    'Desserts'=>[196,237],
    'Christmas Cookies'=>[238,257],
    'How Tos'=>[258,279],
];
$page_index = [];
$recipes = [];
$recipe_index = 0;
for($i=17;$i<=298;$i++) {
    $html = file_get_contents('hocr/page-'.str_pad($i,3,"0",STR_PAD_LEFT).'.ppm.hocr');
    $recipe = html_to_recipe_arr($html);
    if (!is_null($recipe) && $recipe['title'] != '') {
        $recipe['filename'] = preg_replace('/\s+/','_',preg_replace("/[^A-Za-z0-9 ]/",'',trim($recipe['title']))).'.md';
        $page_index[$recipe['pagenumber']] = $recipe['filename'];
        foreach($ingredients as $ingredient) {
            if (stristr($recipe['body'],$ingredient)) {
                $recipe['ingredients'][] = $ingredient;
            }
        }
        foreach($categories as $category => $range) {
            if ($recipe['pagenumber'] >= $range[0] && $recipe['pagenumber'] <= $range[1]) {
                $recipe['categories'][] = $category;
            }
        }
        if (stristr($recipe['title'],'continued') || stristr($recipe['title'],'cont.')) {
            $recipe_index--;
            $recipes[$recipe_index]['body'] .= "\n".$recipe['body'];
            $page_index[$recipe['pagenumber']] = $recipes[$recipe_index]['filename'];
        } else {
            $recipes[$recipe_index] = $recipe;
        }
        $recipe_index++;    
    }
}

foreach($recipes as $recipe) {
    $recipe['body'] = str_replace("\n\n  \n","\n\n----\n**Becky Rant:**",$recipe['body']);
    $matches = [];
    if (preg_match('/page ([0-9]+)/',$recipe['body'],$matches)) {
        $anchor = '['.$matches[0].']('.$page_index[$matches[1]].')';
        $recipe['body'] = preg_replace('/page ([0-9]+)/',$anchor,$recipe['body'],1);
    }
    $recipe_markdown = '---'."\n";
    $recipe_markdown .= 'title: '.trim($recipe['title'])."\n";
    $recipe_markdown .= 'images: []'."\n";
    $recipe_markdown .= 'categories: [Book 2, Recipes Without Pictures, '.implode(', ',$recipe['categories']).']'."\n";
    $recipe_markdown .= 'ingredients: ['.implode(', ',$recipe['ingredients'])."]\n";
    $recipe_markdown .= 'book2page: '.trim($recipe['pagenumber'])."\n";
    $recipe_markdown .= '---'."\n\n";
    $recipe_markdown .= trim($recipe['body']);
    file_put_contents('../recipes2/'.$recipe['filename'],$recipe_markdown);
}