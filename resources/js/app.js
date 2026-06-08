import Swal from 'sweetalert2'
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

window.Swal = Swal

flatpickr("#dateRange", {
    mode: "range",
    dateFormat: "Y-m-d",
});