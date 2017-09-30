<?php

namespace Utils
{
    class Front
    {
        public static function modal($header, $message, $type)
        {
            if ($type == "normal") {
                echo '<script>$(document).ready(function(){ 
			$("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">'.$header.'</span>");
			$("#myModal .modal-dialog .modal-body").text("'.$message.'");
			$("#myModal").modal("show"); 
		});
		</script>';
            }
            else if ($type == "success") {
                echo '<script>$(document).ready(function(){ 
			$("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">'.$header.'</span>");
			$("#myModal .modal-dialog .modal-body").html("<div class=\"alert alert-success\"><span style=\"font-weight: bold;\">'.$message.'</span></div>");
			$("#myModal").modal("show"); 
		});
		</script>';
            }
            else if ($type == "warning") {
                echo '<script>$(document).ready(function(){ 
			$("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">'.$header.'</span>");
			$("#myModal .modal-dialog .modal-body").html("<div class=\"alert alert-warning\"><span style=\"font-weight: bold;\">'.$message.'</span></div>");
			$("#myModal").modal("show"); 
		});
		</script>';
            }
            else if ($type == "danger") {
                echo '<script>$(document).ready(function(){ 
			$("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">'.$header.'</span>");
			$("#myModal .modal-dialog .modal-body").html("<div class=\"alert alert-danger\"><span style=\"font-weight: bold;\">'.$message.'</span></div>");
			$("#myModal").modal("show"); 
		});
		</script>';
            }
        }

        public static function warning($reason)
        {
            \Utils\Front::modal("Uwaga", $reason, "warning");
        }

        public static function error($reason)
        {
            \Utils\Front::modal("Błąd krytyczny", $reason, "danger");
        }

        public static function success($reason)
        {
            \Utils\Front::modal("Powodzenie", $reason, "success");
        }
    }
}