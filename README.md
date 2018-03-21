# ci_calculate_curp

# CALCULATE CURP IN CI LIBRARY

# call library
$this->load->library(array('Curp_library' => 'curp'));

# CONTROLLER
    public function calculate_curp ( ) {
      if( $this->form_validation->run()){
        $data['curp'] = $this->curp->get();
        $this->load->view('curp', $data);
      }
      else{
        error
      }
    }

# FORM
    < form action="subscribe">
      < input type="test" name="name" placeholder="Nombre"><br>
      < input type="test" name="flname" placeholder="Apellido Paterno"><br>
      < input type="test" name="slname" placeholder="Apellido Paterno"><br>
      < input type="date" name="bdate"><br>
      < label>Genero</label>
      < input type="checkbox" name="sex" value="0">Mujer
      < input type="checkbox" name="sex" value="1">Hombre<br>
      < input type="submit" value="Obtener">
    < /form>

# USE
    $this->curp->get();//SABC560626MDFLRN09
    //get first 4 digits
    $this->curp->get('n_initials');//SABC
    //get birth date
    $this->curp->get('b_date');//560626
    //get sex
    $this->curp->get('sex');//M
    //get federal entity
    $this->curp->get('f_entity');//DF
    //get first internal consonants
    $this->curp->get('f_consonants');//LRN
    //get differentiator of homonymy and century
    $this->curp->get('homonymy');//0
    //get check digit
    $this->curp->get('c_digit');
