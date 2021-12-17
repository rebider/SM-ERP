<?php
/*
    PHP port of http://github.com/danpalmer/jquery.complexify.js
    Error reporting is based on https://github.com/kislyuk/node-complexify
    This code is distributed under the WTFPL v2:
*/
namespace App\Common;

class Complexify
{
    private static $MIN_COMPLEXITY = 33;  // 28 8 chars and Number
										  // 33 8 chars with Upper, Lower and Number
    private static $MAX_COMPLEXITY = 120; //  25 chars, all charsets
    public static $CHARSETS = array(
        // Commonly Used
        ////////////////////
        array(0x0020, 0x0020), // Space
        array(0x0030, 0x0039), // Numbers
        array(0x0041, 0x005A), // Uppercase
        array(0x0061, 0x007A), // Lowercase
        array(0x0021, 0x002F), // Punctuation
        array(0x003A, 0x0040), // Punctuation
        array(0x005B, 0x0060), // Punctuation
        array(0x007B, 0x007E), // Punctuation
        // Everything Else
        ////////////////////
        array(0x0080, 0x00FF), // Latin-1 Supplement
        array(0x0100, 0x017F), // Latin Extended-A
        array(0x0180, 0x024F), // Latin Extended-B
        array(0x0250, 0x02AF), // IPA Extensions
        array(0x02B0, 0x02FF), // Spacing Modifier Letters
        array(0x0300, 0x036F), // Combining Diacritical Marks
        array(0x0370, 0x03FF), // Greek
        array(0x0400, 0x04FF), // Cyrillic
        array(0x0530, 0x058F), // Armenian
        array(0x0590, 0x05FF), // Hebrew
        array(0x0600, 0x06FF), // Arabic
        array(0x0700, 0x074F), // Syriac
        array(0x0780, 0x07BF), // Thaana
        array(0x0900, 0x097F), // Devanagari
        array(0x0980, 0x09FF), // Bengali
        array(0x0A00, 0x0A7F), // Gurmukhi
        array(0x0A80, 0x0AFF), // Gujarati
        array(0x0B00, 0x0B7F), // Oriya
        array(0x0B80, 0x0BFF), // Tamil
        array(0x0C00, 0x0C7F), // Telugu
        array(0x0C80, 0x0CFF), // Kannada
        array(0x0D00, 0x0D7F), // Malayalam
        array(0x0D80, 0x0DFF), // Sinhala
        array(0x0E00, 0x0E7F), // Thai
        array(0x0E80, 0x0EFF), // Lao
        array(0x0F00, 0x0FFF), // Tibetan
        array(0x1000, 0x109F), // Myanmar
        array(0x10A0, 0x10FF), // Georgian
        array(0x1100, 0x11FF), // Hangul Jamo
        array(0x1200, 0x137F), // Ethiopic
        array(0x13A0, 0x13FF), // Cherokee
        array(0x1400, 0x167F), // Unified Canadian Aboriginal Syllabics
        array(0x1680, 0x169F), // Ogham
        array(0x16A0, 0x16FF), // Runic
        array(0x1780, 0x17FF), // Khmer
        array(0x1800, 0x18AF), // Mongolian
        array(0x1E00, 0x1EFF), // Latin Extended Additional
        array(0x1F00, 0x1FFF), // Greek Extended
        array(0x2000, 0x206F), // General Punctuation
        array(0x2070, 0x209F), // Superscripts and Subscripts
        array(0x20A0, 0x20CF), // Currency Symbols
        array(0x20D0, 0x20FF), // Combining Marks for Symbols
        array(0x2100, 0x214F), // Letterlike Symbols
        array(0x2150, 0x218F), // Number Forms
        array(0x2190, 0x21FF), // Arrows
        array(0x2200, 0x22FF), // Mathematical Operators
        array(0x2300, 0x23FF), // Miscellaneous Technical
        array(0x2400, 0x243F), // Control Pictures
        array(0x2440, 0x245F), // Optical Character Recognition
        array(0x2460, 0x24FF), // Enclosed Alphanumerics
        array(0x2500, 0x257F), // Box Drawing
        array(0x2580, 0x259F), // Block Elements
        array(0x25A0, 0x25FF), // Geometric Shapes
        array(0x2600, 0x26FF), // Miscellaneous Symbols
        array(0x2700, 0x27BF), // Dingbats
        array(0x2800, 0x28FF), // Braille Patterns
        array(0x2E80, 0x2EFF), // CJK Radicals Supplement
        array(0x2F00, 0x2FDF), // Kangxi Radicals
        array(0x2FF0, 0x2FFF), // Ideographic Description Characters
        array(0x3000, 0x303F), // CJK Symbols and Punctuation
        array(0x3040, 0x309F), // Hiragana
        array(0x30A0, 0x30FF), // Katakana
        array(0x3100, 0x312F), // Bopomofo
        array(0x3130, 0x318F), // Hangul Compatibility Jamo
        array(0x3190, 0x319F), // Kanbun
        array(0x31A0, 0x31BF), // Bopomofo Extended
        array(0x3200, 0x32FF), // Enclosed CJK Letters and Months
        array(0x3300, 0x33FF), // CJK Compatibility
        array(0x3400, 0x4DB5), // CJK Unified Ideographs Extension A
        array(0x4E00, 0x9FFF), // CJK Unified Ideographs
        array(0xA000, 0xA48F), // Yi Syllables
        array(0xA490, 0xA4CF), // Yi Radicals
        array(0xAC00, 0xD7A3), // Hangul Syllables
        array(0xD800, 0xDB7F), // High Surrogates
        array(0xDB80, 0xDBFF), // High Private Use Surrogates
        array(0xDC00, 0xDFFF), // Low Surrogates
        array(0xE000, 0xF8FF), // Private Use
        array(0xF900, 0xFAFF), // CJK Compatibility Ideographs
        array(0xFB00, 0xFB4F), // Alphabetic Presentation Forms
        array(0xFB50, 0xFDFF), // Arabic Presentation Forms-A
        array(0xFE20, 0xFE2F), // Combining Half Marks
        array(0xFE30, 0xFE4F), // CJK Compatibility Forms
        array(0xFE50, 0xFE6F), // Small Form Variants
        array(0xFE70, 0xFEFE), // Arabic Presentation Forms-B
        array(0xFEFF, 0xFEFF), // Specials
        array(0xFF00, 0xFFEF), // Halfwidth and Fullwidth Forms
        array(0xFFF0, 0xFFFD)  // Specials
    );
    // Generated from 500 worst passwords and 370 Banned Twitter lists found at
    // @source http://www.skullsecurity.org/wiki/index.php/Passwords
    private static $BANLIST = array("0000", "1111", "2222", "3333", "4444", "5555", "6666", "7777", "8888", "9999", "1234", "2345", "3456", "4567", "5678", "6789", "aaaa", "bbbb", "cccc", "dddd", "eeee", "ffff", "hhhh", "iiii", "gggg", "kkkk", "llll", "mmmm", "nnnn", "oooo", "pppp", "qqqq", "rrrr", "ssss", "tttt", "uuuu", "vvvv", "wwww", "xxxx", "yyyy", "zzzz", "abcd", "bcde", "cdef", "defg", "efgh", "fghi", "ghij", "hijk", "ijkl", "jklm", "klmn", "lmno", "mnop", "nopq", "opqr", "pqrs", "qrst", "rstu", "stuv", "tuvw", "uvwx", "vwxy", "wxyz");

    private $minimumChars = 8;
    private $strengthScaleFactor = 1;
    public $bannedPasswords = array();
    private $banMode = 'strict'; // (strict|loose)
    private $encoding = 'UTF-8';
	private $minComplexity = 0;

    /**
     * Constructor
     *
     * @param  array  $options  Override default options using an associative array of options
     *
     * Options:
     *  - minimumChars: Minimum password length (default: 8)
     *  - strengthScaleFactor: Required password strength multiplier (default: 1)
     *  - bannedPasswords: Custom list of banned passwords (default: long list of common passwords)
     *  - banMode: Use strict or loose comparisons for banned passwords. "strict" = don't allow a substring of a banned password, "loose" = only ban exact matches (default: strict)
     *  - encoding: Character set encoding of the password (default: UTF-8)
     */
    public function __construct(array $options = array())
    {
        $this->bannedPasswords = self::$BANLIST;
		$this->minComplexity = self::$MIN_COMPLEXITY;

        foreach ($options as $opt => $val) {
            if ($opt === 'banmode') {
                trigger_error('The lowercase banmode option is deprecated. Use banMode instead.', E_USER_DEPRECATED);
                $opt = 'banMode';
            }
            $this->{$opt} = $val;
        }
    }

    /**
     * Determine the complexity added from a character set if it is used in a string
     *
     * @param   string    $str      String to check
     * @param   int[2]    $charset  Array of unicode code points representing the lower and upper bound of the character range
     *
     * @return  int  0 if there are no characters from the character set, size of the character set if there are any characters used in the string
     */
    private function additionalComplexityForCharset($str, $charset)
    {
        $len = mb_strlen($str, $this->encoding);
        for ($i = 0; $i < $len; $i++) {
            $c = unpack('Nord', mb_convert_encoding(mb_substr($str, $i, 1, $this->encoding), 'UCS-4BE', $this->encoding));
            if ($charset[0] <= $c['ord'] && $c['ord'] <= $charset[1]) {
                return $charset[1] - $charset[0] + 1;
            }
        }
        return 0;
    }

    /**
     * Check if a string is in the banned password list
     *
     * @param  string  $str  String to check
     *
     * @return  bool  TRUE if $str is a banned password, or if it is a substring of a banned password and $this->banMode is 'strict'
     */
    private function inBanlist($str, & $bannedCharacters)
    {
        if ($str === '') {
            return false;
        }

        // $str = mb_convert_case($str, MB_CASE_LOWER, $this->encoding);

        if ($this->banMode === 'strict') {
            for ($i = 0; $i < count($this->bannedPasswords); $i++) {
                if (mb_strpos($str, $this->bannedPasswords[$i], 0, $this->encoding) !== false) {
                    $bannedCharacters = $this->bannedPasswords[$i];
                    return true;
                }
            }
            return false;
        } else {
            return in_array($str, $this->bannedPasswords);
        }
    }

    /**
     * Check the complexity of a password
     *
     * @param  string  $password     The password to check
     *
     * @return  object  StdClass object with properties "valid", "complexity", and "error"
     *  - valid: TRUE if the password is complex enough, FALSE if it is not
     *  - complexity: The complexity of the password as a percent
     *  - errors: Array containing descriptions of what made the password fail. Possible values are: banned, toosimple, tooshort
     */
    public function evaluateSecurity($password)
    {
        $complexity = 0;

        if(empty($password) || mb_strlen($password, $this->encoding) < $this->minimumChars){
            return ComplexifyResponse::isFailure(ComplexifyValidStatus::tooShort);
        }

        $bannedCharacters = null;
        // Reset complexity to 0 when banned password is found
        if (!$this->inBanlist($password,$bannedCharacters)) {
            // Add character complexity
            foreach (self::$CHARSETS as $charset) {
                $complexity += $this->additionalComplexityForCharset($password, $charset);
            }
        } else {
            return ComplexifyResponse::isFailure(ComplexifyValidStatus::banned,$bannedCharacters);
        }

        // Use natural log to produce linear scale
        $complexity = log(pow($complexity, mb_strlen($password, $this->encoding))) * (1/$this->strengthScaleFactor);

        if ($complexity <= $this->minComplexity) {
            return ComplexifyResponse::isFailure(ComplexifyValidStatus::tooSimple);
        }
		
		if ($this->minComplexity >= 33){
			if(!preg_match("/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{8,}$/",$password)){
				return ComplexifyResponse::isFailure(ComplexifyValidStatus::tooSimple);
			}
		}

        // Scale to percentage, so it can be used for a progress bar
        $complexity = ($complexity / self::$MAX_COMPLEXITY) * 100;
        $complexity = ($complexity > 100) ? 100 : $complexity;

        return ComplexifyResponse::isSuccess();
    }


}

/**
 * 复杂性响应
 */
class ComplexifyResponse
{
    /** 复杂性验证状态
     * @var ComplexifyValidStatus
     */
    public $validStatus;

    /** 禁止的字符
     * @var
     */
    public $bannedCharacters;

    /**
     * @return ComplexifyResponse
     */
    public static function isSuccess(){
        $r = new ComplexifyResponse();
        $r->validStatus = ComplexifyValidStatus::pass;
        return $r;
    }

    /**
     * @param $validStatus
     * @return ComplexifyResponse
     */
    public static function isFailure($validStatus,$bannedCharacters = null){
        $r = new ComplexifyResponse();
        $r->validStatus = $validStatus;
        $r->bannedCharacters = $bannedCharacters;
        return $r;
    }
}

/**
 * 复杂性验证状态
 */
class ComplexifyValidStatus{

    /**
     * 通过
     */
    const pass = 0;

    /**
     * 密码含有不允许的字符
     */
    const banned = 1;

    /**
     * 密码太过简单
     */
    const tooSimple = 2;

    /**
     * 密码长度太短
     */
    const tooShort = 3;
}

class printComplexifyResult
{
    protected $checkResult;

    public function __construct($checkResult)
    {
        $this->checkResult = $checkResult;
    }

    public function tip()
    {
        switch ($this->checkResult->validStatus)
        {
            case ComplexifyValidStatus::pass:
                return false;
                break;
            case ComplexifyValidStatus::banned:
                return "密码不允许包含：".$this->checkResult->bannedCharacters;
                break;
            case ComplexifyValidStatus::tooSimple:
                return "密码太过简单。";
                break;
            case ComplexifyValidStatus::tooShort:
                return "密码长度太短";
                break;
            default:
                return false;
                break;
        }
    }
}