<?php

class Bot_Data {

    /**
     * @since   1.0.0
     * @access  private
     * @var     resource $file
     */
    private $file;

    /**
     * Bot_Data constructor.
     * @since   1.0.0
     * @access  public
     * @param   resource $file_name The file name.
     */
    public function __construct( $file_name ) {
        $this->file = 'data/' . $file_name;
    }

    /**
     * Open a new file method.
     *
     * @since   1.0.0
     * @access  private
     * @return  resource
     */
    private function file_open() {
        return fopen( $this->file, 'w+' );
    }

    /**
     * Method to store array data to file
     *
     * @since   1.0.0
     * @access  private
     * @param   array $data Array containing data you want to be saved.
     */
    public function store_data( $data ) {
        $handle = $this->file_open();
        fwrite( $handle, json_encode( $data ) );
        fclose( $handle );
    }

    /**
     * Method to get array data from file
     *
     * @since   1.0.0
     * @access  private
     * @return array
     */
    public function get_data() {
        $content = file_get_contents( $this->file );
        return json_decode( $content, true );
    }

}