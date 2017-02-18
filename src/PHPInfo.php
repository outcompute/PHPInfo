<?php
/**
 * The PHPInfo class file.
 *
 * @author     outcompute
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GPL v2
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace OutCompute\PHPInfo;

class PHPInfo
{
    private $_phpInfo;

    public function __construct() {
        $this->_phpInfo = null;
    }

    public function setText($phpInfo) {
        $this->_phpInfo = array(
            'mode' => 'text',
            'data' => $phpInfo
        );
    }

    public function setHTML($phpInfo) {
        $this->_phpInfo = array(
            'mode' => 'html',
            'data' => $phpInfo
        );
    }

    public function get() {
        if(
            is_array($this->_phpInfo) &&
            array_key_exists('mode', $this->_phpInfo)
        ) {
            switch($this->_phpInfo['mode']) {
                case 'text':
                    return $this->_parseText($this->_phpInfo['data']);
                    break;
                case 'html':
                    return $this->_parseHTML($this->_phpInfo['data']);
                    break;
            }
        }
    }

    private function _parseText($phpInfo) {
        $array = array();

        list($header, $body) = explode(' _______________________________________________________________________', $phpInfo);
        if(strlen($header) == 0 || strlen($body) == 0) {
            throw new \Exception(__CLASS__." : Supplied PHPInfo doesn't contain separator ' _______________________________________________________________________'");
        }
        $header = explode(PHP_EOL, trim($header));
        $body = explode(PHP_EOL, trim($body));

        $array = $this->_parseSingleTextBlock($header);

        $processedBody = $this->_parseTextBlocks($body, array('Configuration', 'Environment', 'PHP Variables', 'PHP License'));
        foreach($processedBody as $k => $v) {
            switch($k) {
                case 'Configuration':
                    $modules = $this->_parseTextBlocks($v, get_loaded_extensions());
                    foreach($modules as $moduleName => $moduleSettings)
                        $array['Configuration'][$moduleName] = $this->_parseSingleTextBlock($moduleSettings);
                    break;
                case 'PHP License':
                    array_shift($v);
                    $array[$k] = implode(' ', $v);
                    break;
                default:
                    $array[$k] = $this->_parseSingleTextBlock($v);
                    break;
            }
        }
        return $array;
    }

    private function _parseTextBlocks($blocks, $blockKeys) {
        $settings = array();
        $currentKey = null;
        $currentBlock = array();
        foreach($blocks as $line) {
            $line = trim($line);

            if(in_array($line, $blockKeys)) {
                # Each extension block starts with the name of the extension. And if the current line is such a line, then we
                #   need to start a new block, but before that, we need to process the currentBlock and assign its results
                #   to the currentKey
                if($currentKey != null) {
                    $settings[$currentKey] = $currentBlock; #$this->_parseSingleTextBlock($currentBlock);
                }
                $currentKey = $line;
                $currentBlock = array();
            }

            # If the currentKey is not null, then we are in an extension block, and so this line gets added to the currentBlock
            #   currentKey would be null when this foreach loop starts, and until the first extension block is encountered
            if($currentKey != null)
                $currentBlock[] = $line;
        }
        if($currentKey != null)
            $settings[$currentKey] = $currentBlock; #$this->_parseSingleTextBlock($currentBlock);
        return $settings;
    }

    private function _parseSingleTextBlock($block) {
        $settings = array();
        $currentKey = null;

        foreach($block as $line) {
            $line = trim($line);
            if(strlen($line) > 0) {
                if(strpos($line, '=>') !== false) {
                    $parts = explode('=>', $line);
                    $parts[0] = trim($parts[0]);
                    $parts[1] = trim($parts[1]);
                    switch(count($parts)) {
                        case 2:
                            if(
                                $parts[0] !== 'Variable' &&
                                $parts[1] !== 'Value'
                            ) {
                                $currentKey = $parts[0];
                                $settings[$currentKey] = $parts[1];
                            }
                            break;
                        case 3:
                            $parts[2] = trim($parts[2]);
                            if(
                                $parts[0] !== 'Directive' &&
                                $parts[1] !== 'Local Value' &&
                                $parts[2] !== 'Master Value'
                            ) {
                                $currentKey = $parts[0];
                                $settings[$currentKey] = array(
                                    'Local Value' => $parts[1],
                                    'Master Value' => $parts[2]
                                );
                            }
                            break;
                    }
                } else {
                    if($currentKey != null)
                        $settings[$currentKey] .= $line;
                }
            } else $currentKey = null;
        }
        return $settings;
    }

    private function _parseHTML($phpInfo) {
        # TODO: Implement parsing the HTML format
        return null;
    }
}

?>
