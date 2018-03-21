<?php if (!defined('BASEPATH')) exit('No direct script access allowed');  

/**
 * @author  Jorge Malagon <elddenmedio@gmail.com>
 * @date    2017-12-03
 */
 
 class Curp_library {
    protected $CI;
    public $curp            = '';
    public $n_initials      = '';
    public $b_date          = 0;
    public $sex             = '';
    public $f_entity        = '';
    public $f_consonants    = '';
    public $homonymy        = '';
    public $c_digit         = 0;
    protected $consonants   = array('B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 
                                    'V', 'W', 'X', 'Y', 'Z');
    protected $dictionary   = array('0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, 
                                    '8' => 8, '9' => 9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 
                                    'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 
                                    'M' => 22, 'N' => 23, 'Ñ' => 24, 'O' => 25, 'P' => 26, 'Q' => 27, 'R' => 28, 
                                    'S' => 29, 'T' => 30, 'U' => 31, 'V' => 32, 'W' => 33, 'X' => 34, 'Y' => 35, 
                                    'Z' => 36);
    

    public function __construct() {
        parent::__construct();
        $this->CI			  =& get_instance();
    }

    /**
     * Get
     *
     * Calculate curp or any of their parts
     *
     * @access  public
     * @param   string        -type to get
     * @return  mixed         -string, number
     */
    public function get ( $str = 'curp' ) {
      self::_calculate();

      switch($str){
        case 'n_initials'://first 4 digits
            return $this->n_initials;
            break;
        case 'b_date'://birth date
            return (int) $this->b_date;
            break;
        case 'sex'://sex
            return $this->sex;
            break;
        case 'f_entity'://federal entity
            return $this->f_entity;
            break;
        case 'f_consonants'://first internal consonants
            return $this->f_consonants;
            break;
        case 'homonymy'://differentiator of homonymy and century
            return $this->homonymy;
            break;
        case 'c_digit'://check digit
            return $this->c_digit
            break;
        case 'curp'://curp
        default:
            return $this->curp;
            break;
      }
    }
    
    protected function _calculate ( ) {
        foreach($this->CI->input->post() as $item => $value){
            ${$item}    = $value;
        }

        #get first 4 digits
        
        #validate if firstlastname has space (it´s composed)
        if( ! preg_match('/\s/', $flname)){
            $rfc_1      = substr($flname, 0, 2);
            $_rfc_1     = substr($rfc_1, 0, 1);
            $_rfc_2     = substr($rfc_1, 1, 2);

            #validate if the first character of the firstlastname is Ñ set to X
            if( $_rfc_1 === 'Ñ' || $_rfc_1 === 'ñ'){
                $_rfc_1 = 'X';
            }

            #validate if the firstlastname has vowels
            if( ! in_array(strtoupper($_rfc_2), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
                #find the first vowels
                $ltrim  = ltrim($flname, $_rfc_1);
                $posA   = strpos(strtoupper($ltrim), 'A');
                $posE   = strpos(strtoupper($ltrim), 'E');
                $posI   = strpos(strtoupper($ltrim), 'I');
                $posO   = strpos(strtoupper($ltrim), 'O');
                $posU   = strpos(strtoupper($ltrim), 'U');
                $posA2  = strpos(strtoupper($ltrim), 'Ä');
                $posE2  = strpos(strtoupper($ltrim), 'Ë');
                $posI2  = strpos(strtoupper($ltrim), 'Ï');
                $posO2  = strpos(strtoupper($ltrim), 'Ö');
                $posU2  = strpos(strtoupper($ltrim), 'Ü');

                $min    = min(( (int) ( $posA) ? $posA : 99), ( (int) ( $posE) ? $posE : 99), 
                            ( (int) ( $posI) ? $posI : 99), ( (int) ( $posO) ? $posO : 99), 
                            ( (int) ( $posU) ? $posU : 99), ( (int) ( $posA2) ? $posA2 : 99), 
                            ( (int) ( $posE2) ? $posE2 : 99), ( (int) ( $posI2) ? $posI2 : 99), 
                            ( (int) ( $posO2) ? $posO2 : 99), ( (int) ( $posU2) ? $posU2 : 99));

                if( $min > 0 && $min < 99){
                    $_rfc_w = substr($flname, ($min+1), 1);

                    switch($_rfc_w){
                        case 'Ä':
                            $_rfc_2 = 'A';
                            break;
                        case 'Ë':
                            $_rfc_2 = 'E';
                            break;
                        case 'I':
                            $_rfc_2 = 'I';
                            break;
                        case 'Ö':
                            $_rfc_2 = 'O';
                            break;
                        case 'Ü':
                            $_rfc_2 = 'U';
                            break;
                        default:
                            $_rfc_2 = $_rfc_w;
                            break;
                    }

                }
                else{
                    $_rfc_2 = 'X';
                }
            }
            #validate if the second character is special setting to X
            else{
                $_rfc_2 = ( in_array($_rfc_2, array('/', '-', '.'))) ? 'X' : $_rfc_2;
            }
        }
        else{
            $_last_name = explode(' ', $flname);

            #if first part of firstlastname is preposition omit it
            if( in_array(strtoupper($_last_name[0]), array('DA', 'DAS', 'DE', 'DEL', 'DER', 'DI', 'DIE', 
                                                            'DD', 'EL', 'LA', 'LOS', 'LAS', 'LE', 'MAC', 
                                                            'MC', 'VAN', 'VON', 'Y'))){
                $_rfc_1 = substr($_last_name[1], 0, 1);
                $_rfc_2 = substr($_last_name[1], 1, 2);

                #validate if the second-firstlastname is Ñ set to X
                if( $_rfc_1 === 'Ñ' || $_rfc_1 === 'ñ'){
                    $_rfc_1 = 'X';
                }
                
                #validate if the firstlastname has consonants
                if( ! in_array(strtoupper($_rfc_2), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
                    #find the first consonant
                    $ltrim  = ltrim($flname, $_rfc_1);
                    $posA   = strpos(strtoupper($ltrim), 'A');
                    $posE   = strpos(strtoupper($ltrim), 'E');
                    $posI   = strpos(strtoupper($ltrim), 'I');
                    $posO   = strpos(strtoupper($ltrim), 'O');
                    $posU   = strpos(strtoupper($ltrim), 'U');
                    $posA2  = strpos(strtoupper($ltrim), 'Ä');
                    $posE2  = strpos(strtoupper($ltrim), 'Ë');
                    $posI2  = strpos(strtoupper($ltrim), 'Ï');
                    $posO2  = strpos(strtoupper($ltrim), 'Ö');
                    $posU2  = strpos(strtoupper($ltrim), 'Ü');

                    $min    = min(( (int) ( $posA) ? $posA : 99), ( (int) ( $posE) ? $posE : 99), ( (int) ( $posI) ? $posI : 99), ( (int) ( $posO) ? $posO : 99), ( (int) ( $posU) ? $posU : 99), ( (int) ( $posA2) ? $posA2 : 99), ((int) ( $posE2) ? $posE2 : 99), ( (int) ( $posI2) ? $posI2 : 99), ( (int) ( $posO2) ? $posO2 : 99), ( (int) ( $posU2) ? $posU2 : 99));
                    if( $min > 0 && $min < 99){
                        $_rfc_2 = substr($flname, $min+1, $min+2);
                        $_rfc_2 = ( $_rfc_2 === 'Ä') ? 'A' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ë') ? 'E' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ï') ? 'I' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ö') ? 'O' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ü') ? 'U' : $_rfc_2;
                    }
                    else{
                        $_rfc_2 = 'X';
                    }
                }
                #validate if the second character is special setting to X
                else{
                    $_rfc_2 = ( in_array($_rfc_2, array('/', '-', '.'))) ? 'X' : 'X';
                }
            }
            else{
                $_rfc_1 = substr($_last_name[0], 0, 1);
                $_rfc_2 = substr($_last_name[0], 1, 2);

                #validate if the second-firstlastname is Ñ set to X
                if( $_rfc_1 === 'Ñ' || $_rfc_1 === 'ñ'){
                    $_rfc_1 = 'X';
                }
                
                #validate if the firstlastname has consonants
                if( ! in_array(strtoupper($_rfc_2), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
                    #find the first consonant
                    $ltrim  = ltrim($flname, $_rfc_1);
                    $posA   = strpos(strtoupper($ltrim), 'A');
                    $posE   = strpos(strtoupper($ltrim), 'E');
                    $posI   = strpos(strtoupper($ltrim), 'I');
                    $posO   = strpos(strtoupper($ltrim), 'O');
                    $posU   = strpos(strtoupper($ltrim), 'U');
                    $posA2  = strpos(strtoupper($ltrim), 'Ä');
                    $posE2  = strpos(strtoupper($ltrim), 'Ë');
                    $posI2  = strpos(strtoupper($ltrim), 'Ï');
                    $posO2  = strpos(strtoupper($ltrim), 'Ö');
                    $posU2  = strpos(strtoupper($ltrim), 'Ü');

                    $min    = min(( (int) ( $posA) ? $posA : 99), ( (int) ( $posE) ? $posE : 99), ( (int) ( $posI) ? $posI : 99), ( (int) ( $posO) ? $posO : 99), ( (int) ( $posU) ? $posU : 99), ( (int) ( $posA2) ? $posA2 : 99), ((int) ( $posE2) ? $posE2 : 99), ( (int) ( $posI2) ? $posI2 : 99), ( (int) ( $posO2) ? $posO2 : 99), ( (int) ( $posU2) ? $posU2 : 99));
                    if( $min > 0 && $min < 99){
                        $_rfc_2 = substr($flname, $min+1, $min+2);
                        $_rfc_2 = ( $_rfc_2 === 'Ä') ? 'A' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ë') ? 'E' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ï') ? 'I' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ö') ? 'O' : $_rfc_2;
                        $_rfc_2 = ( $_rfc_2 === 'Ü') ? 'U' : $_rfc_2;
                    }
                    else{
                        $_rfc_2 = 'X';
                    }
                }
                #validate if the second character is special setting to X
                else{
                    $_rfc_2 = ( in_array($_rfc_2, array('/', '-', '.'))) ? 'X' : 'X';
                }
            }
        }
        $rfc_1          = $_rfc_1 . $_rfc_2;

        #validate if slname has space
        if( $slname){
            $rfc_2      = substr($slname, 0, 1);
            $_rfc_1     = substr($rfc_2, 0, 1);
            $rfc_2      = ( $_rfc_1 === 'Ñ' || $_rfc_1 === 'ñ') ? 'X' : $rfc_2;
        }
        else{
            $rfc_2      = 'X';
        }

        #validate if name has space
        if( ! preg_match('/\s/', $name)){
            $rfc_3      = substr($name, 0, 1);
            $_rfc_1     = substr($rfc_3, 0, 1);
            $_rfc_2     = substr($rfc_3, 1, 2);
            if( $_rfc_1 === 'Ñ' || $_rfc_1 === 'ñ'){
                $rfc_3  = 'X' . $_rfc_2;
            }
        }
        else{
            $_name      = explode(' ', $name);
            if( in_array(strtoupper($_name[0]), array('MARIA', 'MA.', 'MA', 'JOSE', 'J.', 'J'))){
                if( $_name[2]){
                    if( $_name[2] === 'Ñ' || $_name[2] === 'ñ'){
                        $rfc_3  = 'X';
                    }
                    else{
                        $rfc_3  = substr($_name[2], 0, 1);
                    }
                }
                else{
                    $rfc_3=substr($_name[1], 0, 1);
                }
            }
            else{
                $rfc_3  = substr($_name[0], 0, 1);
            }
        }

        #validate if the first 4 character fot the RFC is bad word (high-sounding) set it
        if( in_array(strtoupper($rfc_1) . strtoupper($rfc_2) . strtoupper($rfc_3), array('PUTA', 'PUTO', 'PEDO', 
                                                                                            'PEDA', 'PITO', 'CACA', 
                                                                                            'CULO'))){
            $rfc_2      = 'X';
        }

        #get rfc 14-16 characters
        #first consonants
        #firstlastname
        #validate if firstlastname has space (it´s composed)
        $_flname_2      = substr($flname, 1);
        $_flname_2_t    = substr($_flname_2, 0, 1);

        if( ! in_array(strtoupper($_flname_2_t), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
            $_flname_2_t2 = substr($_flname_2, 0, 1);

            if( ! in_array(strtoupper($_flname_2_t2), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
                $_flname_2_t = ( $_flname_2_t === 'Ñ' || $_flname_2_t === 'ñ') ? 'X' : $_flname_2_t;
            }

            $rfc_4      = ( $_flname_2_t === 'Ñ' || $_flname_2_t === 'ñ') ? 'X' : $_flname_2_t;
        }
        else{
            $ltrim      = ltrim($flname, $_flname_2_t);
            $posB       = strpos(strtoupper($ltrim), 'B');
            $posC       = strpos(strtoupper($ltrim), 'C');
            $posD       = strpos(strtoupper($ltrim), 'D');
            $posF       = strpos(strtoupper($ltrim), 'F');
            $posG       = strpos(strtoupper($ltrim), 'G');
            $posH       = strpos(strtoupper($ltrim), 'H');
            $posJ       = strpos(strtoupper($ltrim), 'J');
            $posK       = strpos(strtoupper($ltrim), 'K');
            $posL       = strpos(strtoupper($ltrim), 'L');
            $posM       = strpos(strtoupper($ltrim), 'M');
            $posN       = strpos(strtoupper($ltrim), 'N');
            $posP       = strpos(strtoupper($ltrim), 'P');
            $posQ       = strpos(strtoupper($ltrim), 'Q');
            $posR       = strpos(strtoupper($ltrim), 'R');
            $posS       = strpos(strtoupper($ltrim), 'S');
            $posT       = strpos(strtoupper($ltrim), 'T');
            $posV       = strpos(strtoupper($ltrim), 'V');
            $posW       = strpos(strtoupper($ltrim), 'W');
            $posX       = strpos(strtoupper($ltrim), 'X');
            $posY       = strpos(strtoupper($ltrim), 'Y');
            $posZ       = strpos(strtoupper($ltrim), 'Z');

            $min        = min(( (int) ( $posB) ? $posB : 99), ( (int) ( $posC) ? $posC : 99), 
                                ( (int) ( $posD) ? $posD : 99), ( (int) ( $posF) ? $posF : 99), 
                                ( (int) ( $posG) ? $posG : 99), ( (int) ( $posH) ? $posH : 99), 
                                ( (int) ( $posJ) ? $posJ : 99), ( (int) ( $posK) ? $posK : 99), 
                                ( (int) ( $posL) ? $posL : 99), ( (int) ( $posM) ? $posM : 99),
                                ( (int) ( $posN) ? $posN : 99), ( (int) ( $posP) ? $posP : 99), 
                                ( (int) ( $posQ) ? $posQ : 99), ( (int) ( $posR) ? $posR : 99), 
                                ( (int) ( $posS) ? $posS : 99), ( (int) ( $posT) ? $posT : 99), 
                                ( (int) ( $posV) ? $posV : 99), ( (int) ( $posW) ? $posW : 99), 
                                ( (int) ( $posX) ? $posX : 99), ( (int) ( $posY) ? $posY : 99), 
                                ( (int) ( $posZ) ? $posZ : 99));

            if( $min > 0 && $min < 99){
                $_rfc_f = substr($ltrim, ($min), 1);

                switch($_rfc_f){
                    case 'B':
                        $rfc_4 = 'B';
                        break;
                    case 'C':
                        $rfc_4 = 'C';
                        break;
                    case 'D':
                        $rfc_4 = 'D';
                        break;
                    case 'F':
                        $rfc_4 = 'F';
                        break;
                    case 'G':
                        $rfc_4 = 'G';
                        break;
                    case 'H':
                        $rfc_4 = 'H';
                        break;
                    case 'J':
                        $rfc_4 = 'J';
                        break;
                    case 'K':
                        $rfc_4 = 'K';
                        break;
                    case 'L':
                        $rfc_4 = 'L';
                        break;
                    case 'M':
                        $rfc_4 = 'M';
                        break;
                    case 'N':
                        $rfc_4 = 'N';
                        break;
                    case 'P':
                        $rfc_4 = 'P';
                        break;
                    case 'Q':
                        $rfc_4 = 'Q';
                        break;
                    case 'R':
                        $rfc_4 = 'R';
                        break;
                    case 'S':
                        $rfc_4 = 'S';
                        break;
                    case 'T':
                        $rfc_4 = 'T';
                        break;
                    case 'V':
                        $rfc_4 = 'V';
                        break;
                    case 'W':
                        $rfc_4 = 'W';
                        break;
                    case 'X':
                        $rfc_4 = 'X';
                        break;
                    case 'Y':
                        $rfc_4 = 'Y';
                        break;
                    case 'X':
                        $rfc_4 = 'X';
                        break;
                    default:
                        $rfc_4 = $_rfc_f;
                        break;
                }
            }
            else{
                $rfc_4 = 'X';
            }
        }

        #secondlastname
        #validate if the user has secondlastname or not
        #validate if firstlastname has space (it´s composed)
        if( $slname){
            $_slname_2  = substr($slname, 1);
            $_slname_2_t= substr($_slname_2, 0, 1);

            if( ! in_array(strtoupper($_slname_2_t), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
                $_slname_2_t2 = substr($_slname_2, 0, 1);

                if( ! in_array(strtoupper($_slname_2_t2), array('A', 'E', 'I', 'O', 'U', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'))){
                    $_slname_2_t = ( $_slname_2_t === 'Ñ' || $_slname_2_t === 'ñ') ? 'X' : $_slname_2_t;
                }

                $rfc_5      = ( $_slname_2_t === 'Ñ' || $_slname_2_t === 'ñ') ? 'X' : $_slname_2_t;
            }
            else{
                $ltrim      = ltrim($slname, $_slname_2_t);
                $posB       = strpos(strtoupper($ltrim), 'B');
                $posC       = strpos(strtoupper($ltrim), 'C');
                $posD       = strpos(strtoupper($ltrim), 'D');
                $posF       = strpos(strtoupper($ltrim), 'F');
                $posG       = strpos(strtoupper($ltrim), 'G');
                $posH       = strpos(strtoupper($ltrim), 'H');
                $posJ       = strpos(strtoupper($ltrim), 'J');
                $posK       = strpos(strtoupper($ltrim), 'K');
                $posL       = strpos(strtoupper($ltrim), 'L');
                $posM       = strpos(strtoupper($ltrim), 'M');
                $posN       = strpos(strtoupper($ltrim), 'N');
                $posP       = strpos(strtoupper($ltrim), 'P');
                $posQ       = strpos(strtoupper($ltrim), 'Q');
                $posR       = strpos(strtoupper($ltrim), 'R');
                $posS       = strpos(strtoupper($ltrim), 'S');
                $posT       = strpos(strtoupper($ltrim), 'T');
                $posV       = strpos(strtoupper($ltrim), 'V');
                $posW       = strpos(strtoupper($ltrim), 'W');
                $posX       = strpos(strtoupper($ltrim), 'X');
                $posY       = strpos(strtoupper($ltrim), 'Y');
                $posZ       = strpos(strtoupper($ltrim), 'Z');

                $min        = min(( (int) ( $posB) ? $posB : 99), ( (int) ( $posC) ? $posC : 99), 
                                    ( (int) ( $posD) ? $posD : 99), ( (int) ( $posF) ? $posF : 99), 
                                    ( (int) ( $posG) ? $posG : 99), ( (int) ( $posH) ? $posH : 99), 
                                    ( (int) ( $posJ) ? $posJ : 99), ( (int) ( $posK) ? $posK : 99), 
                                    ( (int) ( $posL) ? $posL : 99), ( (int) ( $posM) ? $posM : 99),
                                    ( (int) ( $posN) ? $posN : 99), ( (int) ( $posP) ? $posP : 99), 
                                    ( (int) ( $posQ) ? $posQ : 99), ( (int) ( $posR) ? $posR : 99), 
                                    ( (int) ( $posS) ? $posS : 99), ( (int) ( $posT) ? $posT : 99), 
                                    ( (int) ( $posV) ? $posV : 99), ( (int) ( $posW) ? $posW : 99), 
                                    ( (int) ( $posX) ? $posX : 99), ( (int) ( $posY) ? $posY : 99), 
                                    ( (int) ( $posZ) ? $posZ : 99));

                if( $min > 0 && $min < 99){
                    $_rfc_f = substr($ltrim, ($min), 1);

                    switch($_rfc_f){
                        case 'B':
                            $_rfc_5 = 'B';
                            break;
                        case 'C':
                            $_rfc_5 = 'C';
                            break;
                        case 'D':
                            $_rfc_5 = 'D';
                            break;
                        case 'F':
                            $_rfc_5 = 'F';
                            break;
                        case 'G':
                            $_rfc_5 = 'G';
                            break;
                        case 'H':
                            $_rfc_5 = 'H';
                            break;
                        case 'J':
                            $_rfc_5 = 'J';
                            break;
                        case 'K':
                            $_rfc_5 = 'K';
                            break;
                        case 'L':
                            $_rfc_5 = 'L';
                            break;
                        case 'M':
                            $_rfc_5 = 'M';
                            break;
                        case 'N':
                            $_rfc_5 = 'N';
                            break;
                        case 'P':
                            $_rfc_5 = 'P';
                            break;
                        case 'Q':
                            $_rfc_5 = 'Q';
                            break;
                        case 'R':
                            $_rfc_5 = 'R';
                            break;
                        case 'S':
                            $_rfc_5 = 'S';
                            break;
                        case 'T':
                            $_rfc_5 = 'T';
                            break;
                        case 'V':
                            $_rfc_5 = 'V';
                            break;
                        case 'W':
                            $_rfc_5 = 'W';
                            break;
                        case 'X':
                            $_rfc_5 = 'X';
                            break;
                        case 'Y':
                            $_rfc_5 = 'Y';
                            break;
                        case 'X':
                            $_rfc_5 = 'X';
                            break;
                        default:
                            $rfc_5 = $_rfc_f;
                            break;
                    }
                }
                else{
                    $rfc_5 = 'X';
                }
            }
        }
        else{
            $rfc_5      = 'X';
        }

        #name
        #validae if has more than one name and if the first name is not maria or jose
        if( preg_match('/\s/', $name)){
            $_name_t    = explode(' ', $name);
            $_name_t1   = substr($name, 0, 1);
            if( in_array(strtoupper($_name_t[0]), array('MARIA', 'MA.', 'MA', 'JOSE', 'J.', 'J'))){
                if( in_array(strtoupper($_name_t[1]), array('DE', 'LA', 'LOS', 'LAS', 'DES'))){
                    $ltrim      = ltrim($_name_t[2], $_name_t1);
                    $posB       = strpos(strtoupper($ltrim), 'B');
                    $posC       = strpos(strtoupper($ltrim), 'C');
                    $posD       = strpos(strtoupper($ltrim), 'D');
                    $posF       = strpos(strtoupper($ltrim), 'F');
                    $posG       = strpos(strtoupper($ltrim), 'G');
                    $posH       = strpos(strtoupper($ltrim), 'H');
                    $posJ       = strpos(strtoupper($ltrim), 'J');
                    $posK       = strpos(strtoupper($ltrim), 'K');
                    $posL       = strpos(strtoupper($ltrim), 'L');
                    $posM       = strpos(strtoupper($ltrim), 'M');
                    $posN       = strpos(strtoupper($ltrim), 'N');
                    $posP       = strpos(strtoupper($ltrim), 'P');
                    $posQ       = strpos(strtoupper($ltrim), 'Q');
                    $posR       = strpos(strtoupper($ltrim), 'R');
                    $posS       = strpos(strtoupper($ltrim), 'S');
                    $posT       = strpos(strtoupper($ltrim), 'T');
                    $posV       = strpos(strtoupper($ltrim), 'V');
                    $posW       = strpos(strtoupper($ltrim), 'W');
                    $posX       = strpos(strtoupper($ltrim), 'X');
                    $posY       = strpos(strtoupper($ltrim), 'Y');
                    $posZ       = strpos(strtoupper($ltrim), 'Z');

                    $min        = min(( (int) ( $posB) ? $posB : 99), ( (int) ( $posC) ? $posC : 99), 
                                        ( (int) ( $posD) ? $posD : 99), ( (int) ( $posF) ? $posF : 99), 
                                        ( (int) ( $posG) ? $posG : 99), ( (int) ( $posH) ? $posH : 99), 
                                        ( (int) ( $posJ) ? $posJ : 99), ( (int) ( $posK) ? $posK : 99), 
                                        ( (int) ( $posL) ? $posL : 99), ( (int) ( $posM) ? $posM : 99),
                                        ( (int) ( $posN) ? $posN : 99), ( (int) ( $posP) ? $posP : 99), 
                                        ( (int) ( $posQ) ? $posQ : 99), ( (int) ( $posR) ? $posR : 99), 
                                        ( (int) ( $posS) ? $posS : 99), ( (int) ( $posT) ? $posT : 99), 
                                        ( (int) ( $posV) ? $posV : 99), ( (int) ( $posW) ? $posW : 99), 
                                        ( (int) ( $posX) ? $posX : 99), ( (int) ( $posY) ? $posY : 99), 
                                        ( (int) ( $posZ) ? $posZ : 99));

                    if( $min > 0 && $min < 99){
                        $_rfc_f = substr($ltrim, ($min), 1);

                        switch($_rfc_f){
                            case 'B':
                                $_rfc_6 = 'B';
                                break;
                            case 'C':
                                $_rfc_6 = 'C';
                                break;
                            case 'D':
                                $_rfc_6 = 'D';
                                break;
                            case 'F':
                                $_rfc_6 = 'F';
                                break;
                            case 'G':
                                $_rfc_6 = 'G';
                                break;
                            case 'H':
                                $_rfc_6 = 'H';
                                break;
                            case 'J':
                                $_rfc_6 = 'J';
                                break;
                            case 'K':
                                $_rfc_6 = 'K';
                                break;
                            case 'L':
                                $_rfc_6 = 'L';
                                break;
                            case 'M':
                                $_rfc_6 = 'M';
                                break;
                            case 'N':
                                $_rfc_6 = 'N';
                                break;
                            case 'P':
                                $_rfc_6 = 'P';
                                break;
                            case 'Q':
                                $_rfc_6 = 'Q';
                                break;
                            case 'R':
                                $_rfc_6 = 'R';
                                break;
                            case 'S':
                                $_rfc_6 = 'S';
                                break;
                            case 'T':
                                $_rfc_6 = 'T';
                                break;
                            case 'V':
                                $_rfc_6 = 'V';
                                break;
                            case 'W':
                                $_rfc_6 = 'W';
                                break;
                            case 'X':
                                $_rfc_6 = 'X';
                                break;
                            case 'Y':
                                $_rfc_6 = 'Y';
                                break;
                            case 'X':
                                $_rfc_6 = 'X';
                                break;
                            default:
                                $rfc_6 = $_rfc_f;
                                break;
                        }
                    }
                    else{
                        $rfc_6 = 'X';
                    }
                }
                else{
                    $ltrim      = ltrim($_name_t[1], $_name_t1);
                    $posB       = strpos(strtoupper($ltrim), 'B');
                    $posC       = strpos(strtoupper($ltrim), 'C');
                    $posD       = strpos(strtoupper($ltrim), 'D');
                    $posF       = strpos(strtoupper($ltrim), 'F');
                    $posG       = strpos(strtoupper($ltrim), 'G');
                    $posH       = strpos(strtoupper($ltrim), 'H');
                    $posJ       = strpos(strtoupper($ltrim), 'J');
                    $posK       = strpos(strtoupper($ltrim), 'K');
                    $posL       = strpos(strtoupper($ltrim), 'L');
                    $posM       = strpos(strtoupper($ltrim), 'M');
                    $posN       = strpos(strtoupper($ltrim), 'N');
                    $posP       = strpos(strtoupper($ltrim), 'P');
                    $posQ       = strpos(strtoupper($ltrim), 'Q');
                    $posR       = strpos(strtoupper($ltrim), 'R');
                    $posS       = strpos(strtoupper($ltrim), 'S');
                    $posT       = strpos(strtoupper($ltrim), 'T');
                    $posV       = strpos(strtoupper($ltrim), 'V');
                    $posW       = strpos(strtoupper($ltrim), 'W');
                    $posX       = strpos(strtoupper($ltrim), 'X');
                    $posY       = strpos(strtoupper($ltrim), 'Y');
                    $posZ       = strpos(strtoupper($ltrim), 'Z');

                    $min        = min(( (int) ( $posB) ? $posB : 99), ( (int) ( $posC) ? $posC : 99), 
                                        ( (int) ( $posD) ? $posD : 99), ( (int) ( $posF) ? $posF : 99), 
                                        ( (int) ( $posG) ? $posG : 99), ( (int) ( $posH) ? $posH : 99), 
                                        ( (int) ( $posJ) ? $posJ : 99), ( (int) ( $posK) ? $posK : 99), 
                                        ( (int) ( $posL) ? $posL : 99), ( (int) ( $posM) ? $posM : 99),
                                        ( (int) ( $posN) ? $posN : 99), ( (int) ( $posP) ? $posP : 99), 
                                        ( (int) ( $posQ) ? $posQ : 99), ( (int) ( $posR) ? $posR : 99), 
                                        ( (int) ( $posS) ? $posS : 99), ( (int) ( $posT) ? $posT : 99), 
                                        ( (int) ( $posV) ? $posV : 99), ( (int) ( $posW) ? $posW : 99), 
                                        ( (int) ( $posX) ? $posX : 99), ( (int) ( $posY) ? $posY : 99), 
                                        ( (int) ( $posZ) ? $posZ : 99));

                    if( $min > 0 && $min < 99){
                        $_rfc_f = substr($ltrim, ($min), 1);

                        switch($_rfc_f){
                            case 'B':
                                $_rfc_6 = 'B';
                                break;
                            case 'C':
                                $_rfc_6 = 'C';
                                break;
                            case 'D':
                                $_rfc_6 = 'D';
                                break;
                            case 'F':
                                $_rfc_6 = 'F';
                                break;
                            case 'G':
                                $_rfc_6 = 'G';
                                break;
                            case 'H':
                                $_rfc_6 = 'H';
                                break;
                            case 'J':
                                $_rfc_6 = 'J';
                                break;
                            case 'K':
                                $_rfc_6 = 'K';
                                break;
                            case 'L':
                                $_rfc_6 = 'L';
                                break;
                            case 'M':
                                $_rfc_6 = 'M';
                                break;
                            case 'N':
                                $_rfc_6 = 'N';
                                break;
                            case 'P':
                                $_rfc_6 = 'P';
                                break;
                            case 'Q':
                                $_rfc_6 = 'Q';
                                break;
                            case 'R':
                                $_rfc_6 = 'R';
                                break;
                            case 'S':
                                $_rfc_6 = 'S';
                                break;
                            case 'T':
                                $_rfc_6 = 'T';
                                break;
                            case 'V':
                                $_rfc_6 = 'V';
                                break;
                            case 'W':
                                $_rfc_6 = 'W';
                                break;
                            case 'X':
                                $_rfc_6 = 'X';
                                break;
                            case 'Y':
                                $_rfc_6 = 'Y';
                                break;
                            case 'Z':
                                $_rfc_6 = 'Z';
                                break;
                            default:
                                $rfc_6 = $_rfc_f;
                                break;
                        }
                    }
                    else{
                        $rfc_6 = 'X';
                    }
                }
            }
            else{
                $ltrim      = ltrim($_name_t[0], $_name_t1);
                $posB       = strpos(strtoupper($ltrim), 'B');
                $posC       = strpos(strtoupper($ltrim), 'C');
                $posD       = strpos(strtoupper($ltrim), 'D');
                $posF       = strpos(strtoupper($ltrim), 'F');
                $posG       = strpos(strtoupper($ltrim), 'G');
                $posH       = strpos(strtoupper($ltrim), 'H');
                $posJ       = strpos(strtoupper($ltrim), 'J');
                $posK       = strpos(strtoupper($ltrim), 'K');
                $posL       = strpos(strtoupper($ltrim), 'L');
                $posM       = strpos(strtoupper($ltrim), 'M');
                $posN       = strpos(strtoupper($ltrim), 'N');
                $posP       = strpos(strtoupper($ltrim), 'P');
                $posQ       = strpos(strtoupper($ltrim), 'Q');
                $posR       = strpos(strtoupper($ltrim), 'R');
                $posS       = strpos(strtoupper($ltrim), 'S');
                $posT       = strpos(strtoupper($ltrim), 'T');
                $posV       = strpos(strtoupper($ltrim), 'V');
                $posW       = strpos(strtoupper($ltrim), 'W');
                $posX       = strpos(strtoupper($ltrim), 'X');
                $posY       = strpos(strtoupper($ltrim), 'Y');
                $posZ       = strpos(strtoupper($ltrim), 'Z');

                $min        = min(( (int) ( $posB) ? $posB : 99), ( (int) ( $posC) ? $posC : 99), 
                                    ( (int) ( $posD) ? $posD : 99), ( (int) ( $posF) ? $posF : 99), 
                                    ( (int) ( $posG) ? $posG : 99), ( (int) ( $posH) ? $posH : 99), 
                                    ( (int) ( $posJ) ? $posJ : 99), ( (int) ( $posK) ? $posK : 99), 
                                    ( (int) ( $posL) ? $posL : 99), ( (int) ( $posM) ? $posM : 99),
                                    ( (int) ( $posN) ? $posN : 99), ( (int) ( $posP) ? $posP : 99), 
                                    ( (int) ( $posQ) ? $posQ : 99), ( (int) ( $posR) ? $posR : 99), 
                                    ( (int) ( $posS) ? $posS : 99), ( (int) ( $posT) ? $posT : 99), 
                                    ( (int) ( $posV) ? $posV : 99), ( (int) ( $posW) ? $posW : 99), 
                                    ( (int) ( $posX) ? $posX : 99), ( (int) ( $posY) ? $posY : 99), 
                                    ( (int) ( $posZ) ? $posZ : 99));

                if( $min > 0 && $min < 99){
                    $_rfc_f = substr($ltrim, ($min), 1);

                    switch($_rfc_f){
                        case 'B':
                            $_rfc_6 = 'B';
                            break;
                        case 'C':
                            $_rfc_6 = 'C';
                            break;
                        case 'D':
                            $_rfc_6 = 'D';
                            break;
                        case 'F':
                            $_rfc_6 = 'F';
                            break;
                        case 'G':
                            $_rfc_6 = 'G';
                            break;
                        case 'H':
                            $_rfc_6 = 'H';
                            break;
                        case 'J':
                            $_rfc_6 = 'J';
                            break;
                        case 'K':
                            $_rfc_6 = 'K';
                            break;
                        case 'L':
                            $_rfc_6 = 'L';
                            break;
                        case 'M':
                            $_rfc_6 = 'M';
                            break;
                        case 'N':
                            $_rfc_6 = 'N';
                            break;
                        case 'P':
                            $_rfc_6 = 'P';
                            break;
                        case 'Q':
                            $_rfc_6 = 'Q';
                            break;
                        case 'R':
                            $_rfc_6 = 'R';
                            break;
                        case 'S':
                            $_rfc_6 = 'S';
                            break;
                        case 'T':
                            $_rfc_6 = 'T';
                            break;
                        case 'V':
                            $_rfc_6 = 'V';
                            break;
                        case 'W':
                            $_rfc_6 = 'W';
                            break;
                        case 'X':
                            $_rfc_6 = 'X';
                            break;
                        case 'Y':
                            $_rfc_6 = 'Y';
                            break;
                        case 'Z':
                            $_rfc_6 = 'Z';
                            break;
                        default:
                            $rfc_6 = $_rfc_f;
                            break;
                    }
                }
                else{
                    $rfc_6 = 'X';
                }
            }
        }
        else{
            $_name_t1   = substr($name, 0, 1);
log_message('error', 'f- ' . $_name_t1);
            $ltrim      = ltrim($name, $_name_t1);
            $posB       = strpos(strtoupper($ltrim), 'B');
            $posC       = strpos(strtoupper($ltrim), 'C');
            $posD       = strpos(strtoupper($ltrim), 'D');
            $posF       = strpos(strtoupper($ltrim), 'F');
            $posG       = strpos(strtoupper($ltrim), 'G');
            $posH       = strpos(strtoupper($ltrim), 'H');
            $posJ       = strpos(strtoupper($ltrim), 'J');
            $posK       = strpos(strtoupper($ltrim), 'K');
            $posL       = strpos(strtoupper($ltrim), 'L');
            $posM       = strpos(strtoupper($ltrim), 'M');
            $posN       = strpos(strtoupper($ltrim), 'N');
            $posP       = strpos(strtoupper($ltrim), 'P');
            $posQ       = strpos(strtoupper($ltrim), 'Q');
            $posR       = strpos(strtoupper($ltrim), 'R');
            $posS       = strpos(strtoupper($ltrim), 'S');
            $posT       = strpos(strtoupper($ltrim), 'T');
            $posV       = strpos(strtoupper($ltrim), 'V');
            $posW       = strpos(strtoupper($ltrim), 'W');
            $posX       = strpos(strtoupper($ltrim), 'X');
            $posY       = strpos(strtoupper($ltrim), 'Y');
            $posZ       = strpos(strtoupper($ltrim), 'Z');

            $min        = min(( (int) ( $posB) ? $posB : 99), ( (int) ( $posC) ? $posC : 99), 
                                ( (int) ( $posD) ? $posD : 99), ( (int) ( $posF) ? $posF : 99), 
                                ( (int) ( $posG) ? $posG : 99), ( (int) ( $posH) ? $posH : 99), 
                                ( (int) ( $posJ) ? $posJ : 99), ( (int) ( $posK) ? $posK : 99), 
                                ( (int) ( $posL) ? $posL : 99), ( (int) ( $posM) ? $posM : 99),
                                ( (int) ( $posN) ? $posN : 99), ( (int) ( $posP) ? $posP : 99), 
                                ( (int) ( $posQ) ? $posQ : 99), ( (int) ( $posR) ? $posR : 99), 
                                ( (int) ( $posS) ? $posS : 99), ( (int) ( $posT) ? $posT : 99), 
                                ( (int) ( $posV) ? $posV : 99), ( (int) ( $posW) ? $posW : 99), 
                                ( (int) ( $posX) ? $posX : 99), ( (int) ( $posY) ? $posY : 99), 
                                ( (int) ( $posZ) ? $posZ : 99));

            if( $min > 0 && $min < 99){
                $_rfc_f = substr($ltrim, ($min), 1);

                switch($_rfc_f){
                    case 'B':
                        $_rfc_6 = 'B';
                        break;
                    case 'C':
                        $_rfc_6 = 'C';
                        break;
                    case 'D':
                        $_rfc_6 = 'D';
                        break;
                    case 'F':
                        $_rfc_6 = 'F';
                        break;
                    case 'G':
                        $_rfc_6 = 'G';
                        break;
                    case 'H':
                        $_rfc_6 = 'H';
                        break;
                    case 'J':
                        $_rfc_6 = 'J';
                        break;
                    case 'K':
                        $_rfc_6 = 'K';
                        break;
                    case 'L':
                        $_rfc_6 = 'L';
                        break;
                    case 'M':
                        $_rfc_6 = 'M';
                        break;
                    case 'N':
                        $_rfc_6 = 'N';
                        break;
                    case 'P':
                        $_rfc_6 = 'P';
                        break;
                    case 'Q':
                        $_rfc_6 = 'Q';
                        break;
                    case 'R':
                        $_rfc_6 = 'R';
                        break;
                    case 'S':
                        $_rfc_6 = 'S';
                        break;
                    case 'T':
                        $_rfc_6 = 'T';
                        break;
                    case 'V':
                        $_rfc_6 = 'V';
                        break;
                    case 'W':
                        $_rfc_6 = 'W';
                        break;
                    case 'X':
                        $_rfc_6 = 'X';
                        break;
                    case 'Y':
                        $_rfc_6 = 'Y';
                        break;
                    case 'Z':
                        $_rfc_6 = 'Z';
                        break;
                    default:
                        $rfc_6 = $_rfc_f;
                        break;
                }
            }
            else{
                $rfc_6 = 'X';
            }
        }

        #get born date
        $_date          = explode('-', $bdate);
        
        $this->n_initials     = strtoupper($rfc_1) . strtoupper($rfc_2) . strtoupper($rfc_3);
        $this->b_date         = substr($_date[0], 2) . str_pad($_date[1], 2, 0, STR_PAD_LEFT) . str_pad($_date[2], 2, 0, STR_PAD_LEFT);
        $this->sex            = strtoupper($sex);
        $this->f_entity       = strtoupper($bentity);
        $this->f_consonants   = strtoupper($rfc_4) . strtoupper($rfc_5) . strtoupper($rfc_6);
        $this->rfc            = $this->n_initials . $this->d_date . $this->sex . $this->f_entity . $this->f_consonants;
        
        #calculate check digit
        $lngSum         = 0.0;
        $lngDigit       = 0.0;
        $rfc_array      = str_split($this->rfc, 1);
        for($k = 0; $k < count($rfc_array); $k++){
            $lngSum     = $lngSum + (int) $this->dictionary[strtoupper($rfc_array[$k])] * (18 - $k);
        }
        $lngDigit       = 10 - $lngSum % 10;
        $_rfc2          = $this->rfc . ( $lngDigit == 10) ? 0 : (( (int) $_date[0] > 1999) ? 'A' : $lngDigit);
        
        $this->homonymy       = $_rfc2;
        $this->c_digit        = $lnDigit;
        
        $this->curp           = $this->curp . $this->homonymy . $this->c_digit;
    }
 }
