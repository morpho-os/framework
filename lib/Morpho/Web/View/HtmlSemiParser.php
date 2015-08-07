<?php
namespace Morpho\Web\View;

use Zend\Filter\AbstractFilter as BaseFilter;

/**
 * This class is changed version of HTML_SemiParser class originally written by Dmitry Koterov:
 * http://forum.dklab.ru/users/DmitryKoterov/, original code was found at:
 * https://github.com/DmitryKoterov/html_formpersister
 */
class HtmlSemiParser extends BaseFilter {
    protected $tagHandlerPrefix = 'tag';
    protected $containerHandlerPrefix = 'container';

    /**
     * Characters inside tag RE (between < and >).
     */
    protected $regexpTagIn = '(?>(?xs) (?> [^>"\']+ | " [^"]* " | \' [^\']* \' )* )';

    /**
     * Containers, whose bodies are not parsed by the library.
     */
    protected $ignoredTags = array('script', 'iframe', 'textarea', 'select', 'title');
    protected $skipIgnoredTags = true;

    private $tagHandlers = array();
    private $containerHandlers = array();
    private $sp_precachers = array();

    private $replaceHash; // unique hash to replace all the tags
    private $spIgnored;

    public function __construct() {
        $this->selfAdd = true;
        $this->attachHandler($this);
        unset($this->selfAdd);

        // Generate unique hash.
        static $num = 0;
        $uniq = md5(microtime() . ' ' . ++$num . ' ' . getmypid());
        $this->replaceHash = $uniq;
    }

    /**
     * Adds new tag handler for future processing.
     *
     * Handler is a callable which is will be for each tag found in the
     * parsed document. This callable could be used to replace tag. Here is
     * the prototype:
     *
     * mixed handler(array $attributes)
     *
     * Callback get 1 parameter - parsed tag attribute array.
     * The following types instead of "mixed" is supported:
     *
     * - NULL  If handler returns null, source tag is not modified.
     * - false If handler returns false,  source tag will be removed.
     * - string        Returning value is used t replace original tag.
     * - array         Returning value is treated as associative array of
     *                 tag attributes. Array also contains two special
     *                 elements:
     *                 - "_tagName": name of tag;
     *                 - "_text":    string representation of tag body
     *                               (for containers only, see below).
     *                               String representation of tag will be
     *                               reconstructed automatically by that array.
     *
     * @param string $tagName Name of tag to handle. E.g., "a", "img" etc.
     * @param callback $handler Callback which will be called on for found tag.
     * @return void
     */
    public function attachTagHandler($tagName, callable $handler, $atFront = false) {
        $tagName = strtolower($tagName);
        if (!isset($this->tagHandlers[$tagName])) {
            $this->tagHandlers[$tagName] = array();
        }
        if (!$atFront) {
            array_push($this->tagHandlers[$tagName], $handler);
        } else {
            array_unshift($this->tagHandlers[$tagName], $handler);
        }
    }

    /**
     * Add the container handler.
     *
     * Containers are processed just like simple tags (see addTag()), but they also have
     * bodies saved in "_text" attribute.
     *
     * @param string $contName Name of container to search.
     * @param callback $handler Call this function to replace.
     * @return void
     */
    public function attachContainerHandler($tagName, $handler, $atFront = false) {
        $tagName = strtolower($tagName);
        if (!isset($this->containerHandlers[$tagName])) {
            $this->containerHandlers[$tagName] = array();
        }
        if (!$atFront) {
            array_push($this->containerHandlers[$tagName], $handler);
        } else {
            array_unshift($this->containerHandlers[$tagName], $handler);
        }
    }

    public function attachHandler($obj, $noPrecache = false, $atFront = false) {
        foreach (get_class_methods($obj) as $method) {
            if (0 === strpos($method, $this->tagHandlerPrefix)) {
                $this->attachTagHandler(
                    substr($method, strlen($this->tagHandlerPrefix)),
                    array($obj, $method),
                    $atFront
                );
            }
            if (0 === strpos($method, $this->containerHandlerPrefix)) {
                $this->attachContainerHandler(
                    substr($method, strlen($this->containerHandlerPrefix)),
                    array($obj, $method),
                    $atFront
                );
            }
        }
        // Add object precacher & post-processors if present.
        if (!isset($this->selfAdd)) {
            $pNames = array(
                'preCacheTags' => 'sp_precachers',
            );
            foreach ($pNames as $pname => $var) {
                if (method_exists($obj, $pname)) {
                    if (!$atFront) {
                        array_push($this->$var, array($obj, $pname));
                    } else {
                        array_unshift($this->$var, array($obj, $pname));
                    }
                }
            }
        }
        return $obj;
    }

    /**
     * Processes a HTML string and calls all attached handlers.
     *
     * @param string $html
     * @return string Text after all replacements.
     */
    public function filter($html) {
        $reTagIn = $this->regexpTagIn;

        // Remove ignored container bodies from the string.
        $this->spIgnored = array();
        if ($this->skipIgnoredTags) {
            $reIgnoredNames = join("|", $this->ignoredTags);
            $reIgnored = "{(<($reIgnoredNames) (?> \s+ $reTagIn)? >) (.*?) (</\\2>)}six";
            // Note that we MUST increase backtrack_limit, else error
            // PREG_BACKTRACK_LIMIT_ERROR will be generated on large SELECTs
            // (see preg_last_error() in PHP5).
            $oldLimit = ini_get('pcre.backtrack_limit');
            ini_set('pcre.backtrack_limit', 1024 * 1024 * 10);
            $html = preg_replace_callback(
                $reIgnored,
                array($this, "ignoredTagsToHash"),
                $html
            );
            ini_set('pcre.backtrack_limit', $oldLimit);
        }
        $sp_ignored = array(
            $this->spIgnored,
            array_keys($this->spIgnored),
            array_values($this->spIgnored)
        );
        unset($this->spIgnored);

        // Replace tags and containers.
        $hashlen = strlen($this->replaceHash) + 10;
        $reTagNames = join("|", array_keys($this->tagHandlers));
        $reConNames = join("|", array_keys($this->containerHandlers));
        $infos = array();
        // (? >...) [without space] is much faster than (?:...) in this case.
        if ($this->tagHandlers) {
            $infos["tagHandlers"] = "/( <($reTagNames) (?> (\s+ $reTagIn) )? > () )/isx";
        }
        if ($this->containerHandlers) {
            $infos["containerHandlers"] = "/(<($reConNames)(?>(\s+$reTagIn))?>(.*?)(?:<\\/\\2\\s*>|\$))/is";
        }
        foreach ($infos as $src => $re) {
            // Split buffer into tags.
            $chunks = preg_split($re, $html, 0, PREG_SPLIT_DELIM_CAPTURE);
            $textParts = array($chunks[0]); // unparsed text parts
            $foundTags = array(); // found tags
            for ($i = 1, $n = count($chunks); $i < $n; $i += 5) {
                // $i points to sequential tag (or container) subchain.
                $tOrig = $chunks[$i]; // - original tag text
                $tName = $chunks[$i + 1]; // - tag name
                $tAttr = $chunks[$i + 2]; // - tag attributes
                $tBody = $chunks[$i + 3]; // - container body
                $tFollow = $chunks[$i + 4]; // - following unparsed text block

                // Add tag to array for precaching.
                $tag = array();
                $this->parseAttrib($tAttr, $tag);
                $tag['_orig'] = $tOrig;
                $tag['_tagName'] = $tName;
                if ($src == "containerHandlers") {
                    if (strlen($tBody) < $hashlen && isset($sp_ignored[0][$tBody])) {
                        // Maybe it is temporarily removed content - place back!
                        // Fast solution working in most cases (key-based hash lookup
                        // is much faster than str_replace() below).
                        $tBody = $sp_ignored[0][$tBody];
                    } else {
                        // We must pass unmangled content to container processors!
                        $tBody = str_replace($sp_ignored[1], $sp_ignored[2], $tBody);
                    }
                    $tag['_text'] = $tBody;
                } elseif (substr($tAttr, -1) == '/') {
                    $tag['_text'] = null;
                }
                $foundTags[] = $tag;
                $textParts[] = $tFollow;
            }

            // Save original tags.
            $origTags = $foundTags;

            // Precache (possibly modifying) all the found tags (if needed).
            $this->precacheTags($foundTags);

            // Process all found tags and join the buffer.
            $html = $textParts[0];
            for ($i = 0, $n = count($foundTags); $i < $n; $i++) {
                $tag = $this->runHandlersForTag($foundTags[$i]);
                if (false === $tag) {
                    // Remove tag.
                    $html = rtrim($html);
                } elseif (!is_array($tag)) {
                    // String representation.
                    $html .= $tag;
                } else {
                    $prefix = isset($tag['_prefix']) ? $tag['_prefix'] : "";
                    unset($tag['_prefix']);
                    $suffix = isset($tag['_suffix']) ? $tag['_suffix'] : "";
                    unset($tag['_suffix']);
                    if (!isset($tag['_orig']) || $tag !== $origTags[$i]) {
                        // Build the tag back if it is changed.
                        $text = $this->makeTag($tag);
                    } else {
                        // Else - use original tag string.
                        // We use this algorythm because of non-unicode tag parsing mode:
                        // e.g. entity &nbsp; in tag attributes is replaced by &amp;nbsp;
                        // in makeTag(), but if the tag is not modified at all, we do
                        // not care and do not call makeTag() at all saving original &nbsp;.
                        $text = $tag['_orig'];
                    }
                    $html .= $prefix . $text . $suffix;
                }
                $html .= $textParts[$i + 1];
            }
        }

        // Return temporarily removed containers back.
        $html = str_replace($sp_ignored[1], $sp_ignored[2], $html);

        return $html;
    }

    /**
     * Recreate the tag or container by its parsed attributes.
     *
     * If $attr[_text] is present, make container.
     *
     * @param array $attr Attributes of tag. These attributes could
     *                      include two special attributes:
     *                      '_text':    tag is a container with body.
     *                                  If null - <tag ... />.
     *                                  If not present - <tag ...>.
     *                      '_tagName': name of this tag.
     *                      '_orig':    ignored (internal usage).
     *
     * @return  HTML-strict representation of tag or container.
     */
    protected function makeTag($attr) {
        // Join & return tag.
        $s = "";
        foreach ($attr as $k => $v) {
            if ($k == "_text" || $k == "_tagName" || $k == "_orig") {
                continue;
            }
            $s .= " " . $k;
            if ($v !== null) {
                $s .= '="' . $this->escapeHtml($v) . '"';
            }
        }
        if (!@$attr['_tagName']) {
            $attr['_tagName'] = "???";
        }

        if (!array_key_exists('_text', $attr)) { // do not use isset()!
            $tag = "<{$attr['_tagName']}{$s}>";
        } elseif ($attr['_text'] === null) { // null
            $tag = "<{$attr['_tagName']}{$s} />";
        } else {
            $tag = "<{$attr['_tagName']}{$s}>{$attr['_text']}</{$attr['_tagName']}>";
        }
        return $tag;
    }

    /**
     * Virtual user-defined client precache functions.
     *
     * This function is called after all tags and containers are
     * found in HTML text, but BEFORE any replaces. It could work with
     * $foundTags to process all found data at once (for
     * faster replacing later). E.g., if callbacks use MySQL, it is
     * much more faster to perform one SQL-query with big IN() clause
     * than a lot of simple SQL querise with their own get_result()
     * calls.
     *
     * @return void
     */
    protected function precacheTags(&$foundTags) {
        foreach ($this->sp_precachers as $pk) {
            // call_user_func() does not support &-parameters
            // while allow_call_time_pass_reference=false
            call_user_func_array($pk, array(&$foundTags));
        }
    }

    /**
     * Replace found ignored container body by hash value.
     *
     * Container's open and close tags are NOT modified!
     * Later hash value will be replaced back to original text.
     */
    protected function ignoredTagsToHash($m) {
        static $counter = 0;
        $hash = $this->replaceHash . ++$counter . "|";
        // DO NOT use chr(0) here!!!
        $this->spIgnored[$hash] = $m[3];
        return $m[1] . $hash . $m[4];
    }

    /**
     * Process the tag.
     *
     * @param array $attr Parsed tag.
     * @return                Attributes of processed tag.
     */
    protected function runHandlersForTag(array $tag) {
        $tagName = strtolower($tag['_tagName']);
        // Processing tag or container?
        if (isset($tag['_text'])) {
            $handlers = $this->containerHandlers[$tagName];
        } else {
            $handlers = $this->tagHandlers[$tagName];
        }
        // Use all handlers from right to left.
        for ($i = count($handlers) - 1; $i >= 0; $i--) {
            $handler = $handlers[$i];
            $result = call_user_func($handler, $tag, $tagName);
            if (null !== $result) {
                if (!is_array($result)) {
                    return $result;
                }
                $tag = $result;
            }
        }
        return $tag;
    }

    /**
     * Parse the attribute string: "a1=v1 a2=v2 ..." of the tag.
     *
     * @param  $body     Tag body between < and >.
     * @param  &$attr    Resulting Array of tag attributes
     * @return void.
     */
    protected function parseAttrib($body, &$attr) {
        $preg = '/([-\w:]+) \s* ( = \s* (?> ("[^"]*" | \'[^\']*\' | \S*) ) )?/sx';
        $regs = null;
        preg_match_all($preg, $body, $regs);
        $names = $regs[1];
        $checks = $regs[2];
        $values = $regs[3];
        $attr = array();
        for ($i = 0, $c = count($names); $i < $c; $i++) {
            $name = strtolower($names[$i]);
            if (!@$checks[$i]) {
                $value = $name;
            } else {
                $value = $values[$i];
                if ($value[0] == '"' || $value[0] == "'") {
                    $value = substr($value, 1, -1);
                }
            }
            if (strpos($value, '&') !== false) {
                $value = $this->unescapeHtml($value);
            }
            $attr[$name] = $value;
        }
    }

    protected function escapeHtml($value) {
        return htmlspecialchars($value, ENT_QUOTES);
    }

    /**
     * Inverses effect of escapeHtml().
     */
    protected function unescapeHtml($value) {
        return htmlspecialchars_decode($value, ENT_QUOTES);
    }
}
