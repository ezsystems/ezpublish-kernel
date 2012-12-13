<?php

/**
 * pretty prints a json string
 *
 * @param string $json
 */
function indent($json)
{

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++)
    {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\')
        {
            $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
        }
        else if(($char == '}' || $char == ']') && $outOfQuotes)
        {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++)
            {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes)
        {
            $result .= $newLine;
            if ($char == '{' || $char == '[')
            {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++)
            {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}

/**
 * converts a DOMElement to an array representation
 *
 * @param \DOMElement $root
 */
function dom_to_array($root)
{
    // if the node has only a single text node
    if(!$root->hasAttributes() && $root->childNodes->length==1
    && $root->childNodes->item(0)->nodeType == XML_TEXT_NODE)
    {
        return $root->childNodes->item(0)->nodeValue;
    }

    $result = array();

    if ($root->hasAttributes())
    {
        $attrs = $root->attributes;

        foreach ($attrs as $i => $attr)
        $result["_" . $attr->name] = $attr->value;
    }

    $children = $root->childNodes;

    $group = array();

    $text = "";

    for($i = 0; $i < $children->length; $i++)
    {
        $child = $children->item($i);
        if($child->nodeType == XML_TEXT_NODE)
        {
            $text = $text . $child->nodeValue;
        }
        else
        {
            if (!isset($result[$child->nodeName]))
                $result[$child->nodeName] = dom_to_array($child);
            else
            {
                if (!isset($group[$child->nodeName]))
                {
                    $tmp = $result[$child->nodeName];
                    $result[$child->nodeName] = array($tmp);
                    $group[$child->nodeName] = 1;
                }

                $result[$child->nodeName][] = dom_to_array($child);
            }
        }
    }
    $trimmed = trim($text);
    if($trimmed != "")
    $result['#text'] = $text;
    return $result;
}
/**
 * takes a file name of an xml document and returns an json representation
 *
 * @param string $fileName
 */
function convert($fileName)
{
    $d = new DOMDocument(1, "UTF-8");
    $d->load($fileName);
    $ret[$d->documentElement->nodeName] = dom_to_array($d->documentElement);
    return json_encode($ret);
}

echo indent(str_replace("\/","/",convert($argv[1])));

?>
