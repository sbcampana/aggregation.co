<?php
require("include/db.php");
require("include/header.php");
require("include/nav.php");
require("include/rss_util.php");

// Create a function that will filter out the news sources we don't want to see based on a checkbox menu
// The checkbox menu will run a query to pull the number of distinct news sources we have in the table

// This will be a function that returns the number of distinct feed titles from the items database which will be used to generate a check box for each
function get_sources($db){
    $source_query = "SELECT DISTINCT feedTitle FROM items";
    $sources = Query($db, $source_query);
    return $sources;
}

// Create a form around this for a POST, and add the chunk of code above that will check if submit has been pressed and if it has add the new rss feed to the database and run fetshsimplepie script
echo "<label>Add an RSS link to your feed</label>\t";
echo "<input name='input' id='input' placeholder='RSS Link'>";
// add a submit button that will update the database


$sources = get_sources($db);
echo "<div class='row'>";
echo"<div class='col-md-6'>";
foreach($sources as $source){
    // check boxes for the news sources go here
    // type=checkbox
    // name=sourcesList[]
    // value=$source
    // id=$source
    // The label is $source
    echo "<label>";
    //checkbox goes here
    echo "</label>";
}
echo "</div>";
echo "</div>";

echo "<div id=\"content\">\n";
    echo "<div id=\"content-left\">\n";

        $query = "SELECT items.id AS id,feedTitle,feedLink,itemTitle,itemPubDate,itemLink,itemDesc FROM feeds,items WHERE feeds.displayColumn=1 AND feeds.id=items.id";
        //create a for loop that will run for each element in the list of items that need to be added to the where clause because those the the news sources we want to include.
        DisplayColumn($db, $query);

    echo "</div>\n";
    echo "<div id=\"content-middle\">\n";

        $query = "SELECT items.id AS id,feedTitle,feedLink,itemTitle,itemPubDate,itemLink,itemDesc FROM feeds,items WHERE feeds.displayColumn=2 AND feeds.id=items.id";
        DisplayColumn($db, $query);

    echo "</div>\n";
    echo "<div id=\"content-right\">\n";

        $query = "SELECT items.id AS id,feedTitle,feedLink,itemTitle,itemPubDate,itemLink,itemDesc FROM feeds,items WHERE feeds.displayColumn=3 AND feeds.id=items.id";
        DisplayColumn($db, $query);

    echo "</div>\n";
echo "</div>\n";

function DisplayColumn($db, $query)
{
	if (isset($_GET['feed'])) {
		$query .= " WHERE id=" . $_GET['feed'];
	}
	$rows = Query($db, $query);
	$rssItems = LoadCachedItems($rows);

	$prev = NULL;
	foreach ($rssItems as $item) {
		DisplayItem($prev, $item);
		$prev = $item;
	}
}

function DisplayItem($prev, $item)
{
    echo "<article>";

    // Separator (or not) and feed title
    if ($prev == NULL || $prev['feedTitle'] != $item['feedTitle'] ) {
	echo "<div class=\"itemSep\"></div>\n";

	// Feed favicon.ico
	$url = preg_replace('/^https?:\/\//', '', $item['feedLink']);
	if ($url != "") {
		$imgurl = "https://www.google.com/s2/favicons?domain=";
		$imgurl .= $url;

		echo "<div class=\"feedIcon\">";
		"\" type=\"image/x-icon\"></div>\n";
		echo '<img src="';
		echo $imgurl;
		echo '" width="16" height="16" />';
		echo "</div>\n";
	}

	// Feed title
	if (($item['feedTitle'] != NULL) &&
	    (strlen($item['feedTitle']) > 0)) {
		echo "<span class=\"feedTitle\">" .
			"<a href=\"http://aggregation.co?feed=" .
			$item['id'] . "\">" .  $item['feedTitle'] .
			"</a></span>\n";
	}
    }
    // Item pub date
    date_default_timezone_set("America/Denver");
    echo "<span class=\"itemPubDate\">" .
	date("M j  g:ia", strtotime($item['itemPubDate'])) .
	"</span>\n";

    // Item title
    echo "<div class=\"itemTitle\">";

    if (($item['itemTitle'] != NULL) && (strlen($item['itemTitle']) > 0)) {

        if ($item['itemLink'] != NULL)
	    echo "<a href=\"" . $item['itemLink'] . "\">";

	echo $item['itemTitle'];

        if ($item['itemLink'] != NULL)
	    echo "</a>";

    }
    echo "</div>\n";

    // Item description
    echo "<div class=\"itemDesc\">" . $item['itemDesc'] . "</div>\n";
    echo "</article>\n";
}

require("include/footer.php");
