<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\models;

use app\models as Model;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Models\Traits\DBModel;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Admins
 */
class Admins extends Models implements IModels {
    use DBModel;

    /**
     * Directorio de las imágenes
     * @var string
     */

    const IMAGE_DIR = '../assets/app/img/profile/';


    /**
     * Verifica un correo electrónico
     * 
     * @param string $email : Email
     * @param bool|bool $edit : True - editar, False - agregar
     * 
     * @throws ModelsException cuando el email no es válido
     */
    public function checkEmail(string $email, bool $edit = false){
        # Verificar formato
        if (!Helper\Strings::is_email($email)) {
            throw new ModelsException('El email no tiene un formato válido.');
        }

        # Verifica existencia
        $email = $this->db->scape($email);
        $where = $edit ? "AND id_user <> '$this->id'" : '';
        if (false != $this->db->select('id_user', 'users', null, "email = '$email' $where", 1)) {
            throw new ModelsException('El email introducido ya existe.');
        }
    }

    /**
     * Revisa si las contraseñas son iguales
     *
     * @param string $pass : Contraseña sin encriptar
     * @param string $pass_repeat : Contraseña repetida sin encriptar
     *
     * @throws ModelsException cuando las contraseñas no coinciden
     */
    private function checkPassMatch(string $pass, string $pass_repeat) {
        if ($pass != $pass_repeat) {
            throw new ModelsException('Las contraseñas no coinciden.');
        }
    }
    /**
     * Verifica una fecha 
     * 
     * @param string $birthdate ; Fecha a verificar
     * 
     * @throws ModelsException cuando la fecha no sea válida
     */
    private function checkBirthdate(string $birthdate) {
        # Verificar formato
        if (!preg_match('/^([0-9]{2}\/[0-9]{2}\/[0-9]{4})$/', $birthdate)) {
            throw new ModelsException('El formato de la fecha debe ser DD/MM/YYYY.');
        }

        # Convertimos en array
        $date = explode('/', $birthdate);
        # Validamos fecha
        if (!checkdate($date[1], $date[0], $date[2])) {
            throw new ModelsException('La fecha seleccionada es inválida.');
        }
    }
    /**
     * Verifica el género
     * 
     * @param string $gender : Género
     * 
     * @throws ModelsException cuando la el género es inválido
     */
    private function checkGender(string $gender){
        # Existencia del género
        if (!in_array($gender, ['male', 'female'])) {
            throw new ModelsException('Debe escoger un género.');
        }
    }

    /**
     * Verificar una imagen de perfil
     * 
     * @param null|object $image: Imagen a subir
     *  
     * @throws ModelsException cuando la imagen no tiene un formato o tamaño válido
     */

    private function checkImage($image){
        # Foto en caso de ser subida
        if(null != $image) {
            # Formato
            if(!Helper\Files::is_image($image->getClientOriginalName())) {
                throw new ModelsException('La foto de perfil debe ser de formato JPG, PNG o GIF.'); 
            }
            # Peso max 500Kb
            if($image->getClientSize() > 500000) {
                throw new ModelsException('El peso máximo de la foto de perfil debe ser de 500KB');
            }
        }
    }


    /**
     * Sube la foto de perfil de un usuario
     * 
     * @param null|object $image : objecto de la imagen a subir
     * @param int $id_user : ID del usuario
     * 
     * @return void
     */
    private function uploadPhoto($image, int $id_user) {
        if(null != $image) {

            # Nombre unico sin problemas para la imagen
            $name = time() . '.' . $image->getClientOriginalExtension();

            # Ruta de subida
            $ruta = '../'. self::IMAGE_DIR . $id_user . '/';

            # Eliminar cualquier posible carpeta de la ruta
            if (is_dir($ruta)) {
                Helper\Files::rm_dir($ruta);
            }
           

            # Crear la carpeta
            Helper\Files::create_dir($ruta);

            # Subir el archivo
            $image->move($ruta, $name);

            # Actualizar en la base de datos
            $this->db->update('users',array(
                'image' => $name
            ),"id_user='$id_user'",1);
        }
    }

    /**
     * Crea un administrador
     * 
     * @return array
    */ 
    public function create() : array {
        try {
            global $http;

            $email = $http->request->get('email');
            $pass = $http->request->get('pass');
            $pass_repeat = $http->request->get('pass_repeat');
            $gender = $http->request->get('gender');
            $birthdate = $http->request->get('birthdate');
            $first_name = $http->request->get('first_name');
            $last_name = $http->request->get('last_name');
            $perfil = $http->files->get('perfil');
            
            # Verificar campos vacíos
            if (!Helper\Functions::all_full($http->request->all())) {
                throw new ModelsException('Todos los campos con * son requeridos.');
            }

            # Verificar correo
            $this->checkEmail($email);

            # Verificar contraseñas
            $this->checkPassMatch($pass, $pass_repeat);

            # Verificar género
            $this->checkGender($gender);

            # Verificar fecha de nacimiento
            $this->checkBirthdate($birthdate);

            # Verificar imagen
            $this->checkImage($perfil);

            # Insertamos datos
            $id_user = $this->db->insert('users', array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'pass' => Helper\Strings::hash($pass),
                'birthdate' => strtotime( str_replace('/', '-', $birthdate) ),
                'gender' => $gender,
                'is_admin' => '1',
                'created_at' => time()
            ));

            # Subir imagen si existe
            $this->uploadPhoto($perfil, $id_user);


            return array('success' => 1, 'message' => 'Creado con éxito.');
        } catch(ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }
    }

    /**
     * Edita un administrador
     * 
     * @return array
    */ 
    public function edit() : array {
        try {
            global $http;

            $this->id = $http->request->get('id_user');

            $email = $http->request->get('email');
            $pass = $http->request->get('pass');
            $pass_repeat = $http->request->get('pass_repeat');
            $gender = $http->request->get('gender');
            $birthdate = $http->request->get('birthdate');
            $first_name = $http->request->get('first_name');
            $last_name = $http->request->get('last_name');
            $perfil = $http->files->get('perfil');


            # Verificar campos vacíos
            if (Helper\Functions::e($email, $gender, $birthdate, $first_name, $last_name)) {
                throw new ModelsException('Todos los campos con * son requeridos.');
            }

            # Verificar correo
            $this->checkEmail($email, true);

            # Verificar género
            $this->checkGender($gender);

            # Verificar fecha de nacimiento
            $this->checkBirthdate($birthdate);

            # Verificar imagen
            $this->checkImage($perfil);

            # Datos a editar
            $data = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'birthdate' => strtotime( str_replace('/', '-', $birthdate) ),
                'gender' => $gender,
                'is_admin' => '1',
                'created_at' => time()
            );

            # Verificación de contraseñas
            if (!Helper\Functions::emp($pass) || !Helper\Functions::emp($pass_repeat)) {
                # Verificar contraseñas
                $this->checkPassMatch($pass, $pass_repeat);

                # Guardamos la contraseña en el array
                $data['pass'] = Helper\Strings::hash($pass);
            }

            # Editamos los datos
            $this->db->update('users', $data, "id_user = '$this->id' AND is_admin = '1'", 1);

            # Subir imagen si existe
            $this->uploadPhoto($perfil, $this->id);
            
            
            return array('success' => 1, 'message' => 'Editado con éxito.');
        } catch(ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }
    }

    /**
     * Obtiene uno o todos los usuarios administradores
     * 
     * @param bool|bool $multi : true para traer todos
     * 
     * @return array con los datos de los admins
     */
    public function get(bool $multi = true) {
        if($multi) {
            return $this->db->select('*','users',null,"is_admin = '1'",null,'ORDER BY created_at ASC'); 
        }

        return $this->db->select('*','users',null,"id_user='$this->id' AND is_admin = '1'",1);
    }
    /**
     * Elimina a un usuario administrador
     * 
     * @return void
     */
    public function delete(){
        global $config;
        $action = '?error=true';
        if ($this->id_user != $this->id) {
            $this->db->delete('users', "id_user = '$this->id' AND is_admin = '1'", 1);
            $action = '?success=true';
        }

        Helper\Functions::redir($config['build']['url'].'admins'.$action);
    }


    /**
     * __construct()
    */
    public function __construct(IRouter $router = null) {
        parent::__construct($router);
		$this->startDBConexion();
    }
}