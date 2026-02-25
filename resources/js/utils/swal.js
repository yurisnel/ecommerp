import Swal from 'sweetalert2';

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

export default {
    success: (message) => {
        return Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            confirmButtonColor: '#10b981'
        });
    },
    
    error: (message) => {
        return Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#ef4444'
        });
    },
    
    warning: (message) => {
        return Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: message,
            confirmButtonColor: '#f59e0b'
        });
    },
    
    info: (message) => {
        return Swal.fire({
            icon: 'info',
            title: 'Information',
            text: message,
            confirmButtonColor: '#3b82f6'
        });
    },
    
    confirm: (message, title = 'Are you sure?') => {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        });
    },
    
    toastSuccess: (message) => {
        return toast.fire({
            icon: 'success',
            title: message
        });
    },
    
    toastError: (message) => {
        return toast.fire({
            icon: 'error',
            title: message
        });
    },
    
    loading: (title = 'Loading...') => {
        return Swal.fire({
            title: title,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },
    
    close: () => {
        Swal.close();
    }
};
