<?php
/**
 * Exif Reader
 *
 * A small exif reader class intended for quick access to common 
 * data in photos aded by cameras.
 *
 * @author Kenth 'keha76' Hagström <keha1976@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 * @version 1.0
 **/
class KEHA76_Exif_Reader {
	/**
	 *   Get general EXIF details
	 *   Example of returned data:
	 *
	 *   Array
	 *   (
	 *      [Make] => NIKON CORPORATION
	 *      [Model] => NIKON D7000
	 *      [FocalLength] => 35 mm
	 *      [Exposure] => 10/500
	 *      [Aperture] => f/1.8
	 *      [ShutterSpeed] => 1/50s
	 *      [Date] => 2013:01:01 10:00:09
	 *      [ISO] => 500
	 *   )
	 *
	 *   @access public
	 *   @param string $imagePath 
	 *   @return void
	**/
	public function getDetails( $imagePath ) {
		
		// Check if the variable is set and if the file itself exists before continuing
		if( file_exists( $imagePath ) )
		{
			// There are 2 arrays which contains the information need, so it's easier to state them both
			$dataIFD0 = exif_read_data( $imagePath , 'IFD0', 0 );
	      	$dataEXIF = exif_read_data( $imagePath , 'EXIF', 0 );
	      	
			// Error control
			$notFound = "Unknown";
			
			// Make 
			if( @array_key_exists( 'Make', $dataIFD0 ) ) {
				$make = $dataIFD0[ 'Make' ];
			} else {
				$make = $notFound;
			}
      
			// Model
			if( @array_key_exists( 'Model', $dataIFD0 ) ) {
				$model = $dataIFD0[ 'Model' ];
			} else {
				$model = $notFound;
			}
	      	
			// Exposure
			if( @array_key_exists( 'ExposureTime', $dataIFD0 ) ) {
				$exposure = $dataIFD0[ 'ExposureTime' ];
	      	} else {
				$exposure = $notFound;
			}

			// Aperture
			if( @array_key_exists( 'ApertureFNumber', $dataIFD0[ 'COMPUTED' ] ) ) {
	        	$aperture = $dataIFD0[ 'COMPUTED' ][ 'ApertureFNumber' ];
	      	} else {
				$aperture = $notFound;
			}
      
	      	// Date
			if( @array_key_exists( 'DateTime', $dataIFD0 ) ) {
				$date = $dataIFD0[ 'DateTime' ];
	      	} else {
				$date = $notFound;
			}
	      	
			// ISO
			if( @array_key_exists( 'ISOSpeedRatings', $dataEXIF ) ) {
	        	$iso = $dataEXIF[ 'ISOSpeedRatings' ];
	      	} else {
				$iso = $notFound;
			}
	      	
			$exif = exif_read_data( $imagePath );
			$shutterSpeed = $this->getShutter( $exif );
			$focalLength = $this->getFocalLength( $exif );
			
			// Construct the data array to return
			$return = array();
	      	$return[ 'make' ]         = $make;
			$return[ 'model' ]        = $model;
			$return[ 'focal_length' ]  = $focalLength;
			$return[ 'exposure' ]     = $exposure;
			$return[ 'aperture' ]     = $aperture;
			$return[ 'shutter_speed' ] = $shutterSpeed;
			$return[ 'date_taken' ]         = $date;
			$return[ 'iso' ]          = $iso;
			$return[ 'f_stop' ]        = $this->getFstop( $exif );
			// Return data
			return $return;
    
	    } else {
			return false; 
		}
	}
	
	/**
	 *   Calculate Float
	 *
	 *   @param integer $value 
	 *   @return void
	**/
	private function getFloat( $value ) { 
		$pos = strpos( $value, '/' );
		if( $pos === false ) {
			return (float)$value;
		} else {
			$a = (float)substr( $value, 0, $pos ); 
			$b = (float)substr( $value, $pos+1 ); 
			return ( $b == 0 ) ? ( $a ) : ( $a / $b );
		}
	}

	/**
	 *   Shutter Speed
	 *
	 *   @param array $exif 
	 *   @return string
	**/
	public function getShutter( $exif )
	{
		if (!isset($exif['ShutterSpeedValue'] ) ) {
			return false;
		}
	  	
		$apex    = $this->getFloat( $exif['ShutterSpeedValue']); 
		$shutter = pow( 2, -$apex );
		
		if( $shutter == 0 ) {
			return false;
		}
		
		if( $shutter >= 1 ) {
			return round( $shutter ) . 's';
		}
		
		return '1/' . round(1 / $shutter) . 's'; 
	}
	
	/**
	 *   Focal Length
	 *
	 *   @param array $exif 
	 *   @return string
	**/
	public function getFocalLength( $exif ) {
		
		if( !isset( $exif[ 'FocalLength' ] ) ) {
			return false;
		}
		
		$focal = explode( '/', $exif['FocalLength'] );
		$focalLength = round( $focal[ 0 ] / $focal[ 1 ] );
		return $focalLength.' mm';
	}
	
	/**
	 *   F-Stop
	 *
	 *   @param array $exif 
	 *   @return void
	**/
	public function getFstop( $exif ) {
		if( !isset( $exif['ApertureValue'] ) ) return false;
	  	$apex  = $this->getFloat( $exif['ApertureValue'] );
		$fstop = pow( 2, $apex/2 );
	  	if( $fstop == 0 ) return false;
		return 'f/' . round( $fstop, 1 );
	}
}

?>