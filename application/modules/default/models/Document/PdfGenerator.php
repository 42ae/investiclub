<?php
/////////////////////////////////////////
// PACKAGE TO INSTALL !!!!             //
// apt-get install texlive             //
// apt-get install texlive-latex-extra //
/////////////////////////////////////////

class Model_Document_PdfGenerator
{
	public $filePath;
	
    public function __construct($inputData, $clubId)
    {
        $templateDir = "/var/www/test/pdf/TEMPLATEDIR";
        $dataDir = "/tmp/DATA_club" . $clubId . "_" . time();
        $this->filePath = $dataDir . "/main.pdf";
        mkdir($dataDir);
        chdir($dataDir);

        $tab = array('_DATE_' => '2011/04/01',
			'_DOC_NAME_' => 'Bilan Tresorerie 04',
			'_CLUB_NAME_' => 'LABANDE A ABDEL',
			'_SECTION_1_' => 'MA BOULE',
			'_SECTION_2_' => 'MON CUL',
			'_SUBSECTION_1_1_' => 'ELLE EST FAT',
			'_SUBSECTION_1_2_' => 'MASSE DE POIL',
			'_SUBSECTION_2_1_' => 'TOUT DOUX',
			'_SUBSECTION_2_2_' => 'VALEUR ANAL',
			'_SUBSUBSECTION_2_1_1_' => 'COMME DE LA PEAU DE BEBE',
			'_SUBSUBSECTION_2_1_2_' => 'SENTIR LA ROSE',
			'_TEXT_1_1_' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc.
			',
			'_TEXT_1_2_' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.

Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu.

In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a.',
			'_TEXT_2_1_' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a.',
			'_TEXT_2_2_' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.

Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate.',
			'_TAB_VAL_' => array('solde' => $inputData['solde'],
                                 'pf' => $inputData['pf']));
    


if ($handle = opendir($templateDir))
  {
    while (false !== ($file = readdir($handle)))
      if ($file != "." && $file != "..")
	{
	  touch($file);
	  chmod($file, 0777);
	  if ($source = fopen("$templateDir/$file", 'r'))
	    {
	      if ($dest = fopen($file, 'r+'))
		{
		  while (($line = fgets($source)) != false)
		    {
		      $flag = 0;
		      foreach ($tab as $cle => $element)
			{
			  if (preg_match("/$cle/", $line) == 1)
			    {
			      if (preg_match("/_TAB_/", $line) == 1)
				{
				  $flag = 1;
				  $cle($line, $dest, $tab);
				}
			      else
				$line = str_replace("$cle", $element, $line);
			    }
			}
		      if ($flag == 0)
			fputs($dest, $line);
		    }
		  fclose($dest);
		}
	      fclose($source);
	    }
	}
    closedir($handle);
  }
///////////////////////////
//SCRIPT TO GENERATE .PDF//
//NEED CHANGE TO MAKE    //
//GENERIC NAME           //
//OR TO CHANGE DIRECTORY //
///////////////////////////
system("pdflatex main.tex > /dev/null");
system("pdflatex main.tex > /dev/null");
system("rm *.aux > /dev/null");
system("rm *.log > /dev/null");
system("rm *.out > /dev/null");
system("rm *.tex > /dev/null");
system("rm *.toc > /dev/null");
//system("mv main.pdf /var/www/test/pdf/output" . time() . ".pdf");

    }

    
}

function _TAB_VAL_($line, $dest, $tab)
{
  fputs($dest, "\\begin{tabular}{|l|l|}\n"); //add |l| if more tab
  fputs($dest, "\\hline\n");
  fputs($dest, "\\textbf{Cle} & \\textbf{Valeur}\\\\\n"); // modif clŽ valeur si besoin
  fputs($dest, "\\hline\n");
  foreach($tab['_TAB_VAL_'] as $cle => $element) {
    fputs($dest, $cle);
    fputs($dest, " & ");
    fputs($dest, $element);
    fputs($dest, "\\\\\n");
    fputs($dest, "\\hline\n");
  }
  fputs($dest, "\\end{tabular}\n");
}

?>