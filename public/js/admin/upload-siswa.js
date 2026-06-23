(function () {
    'use strict';

    const SELECTORS = {
        dropArea: 'dropArea',
        fileInput: 'fileInput',
        dropText: 'dropText',
        dropHint: 'dropHint',
        selectedFileBox: 'selectedFileBox',
        selectedFileName: 'selectedFileName',
        selectedFileSize: 'selectedFileSize',
        removeFileButton: 'removeFileBtn',
        formUpload: 'formUploadSiswa',
        uploadProgress: 'upload-progress',
        uploadStatus: 'upload-status',
        uploadBar: 'upload-bar',
        uploadButton: 'btnUpload'
    };

    const DEFAULT_DROP_TEXT = 'Klik atau tarik file ke sini';
    const DEFAULT_DROP_HINT = 'Format: .xlsx, .xls, .csv | Maksimal 10MB';
    const SELECTED_DROP_TEXT = 'File berhasil dipilih';
    const SELECTED_DROP_HINT = 'File siap diproses untuk prediksi massal';
    const EMPTY_FILE_MESSAGE = 'Silakan pilih file Excel/CSV terlebih dahulu.';

    let uploadTimer = null;

    function getElement(id) {
        return document.getElementById(id);
    }

    function getElements() {
        return {
            dropArea: getElement(SELECTORS.dropArea),
            fileInput: getElement(SELECTORS.fileInput),
            dropText: getElement(SELECTORS.dropText),
            dropHint: getElement(SELECTORS.dropHint),
            selectedFileBox: getElement(SELECTORS.selectedFileBox),
            selectedFileName: getElement(SELECTORS.selectedFileName),
            selectedFileSize: getElement(SELECTORS.selectedFileSize),
            removeFileButton: getElement(SELECTORS.removeFileButton),
            formUpload: getElement(SELECTORS.formUpload),
            uploadProgress: getElement(SELECTORS.uploadProgress),
            uploadStatus: getElement(SELECTORS.uploadStatus),
            uploadBar: getElement(SELECTORS.uploadBar),
            uploadButton: getElement(SELECTORS.uploadButton)
        };
    }

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) {
            return '0 KB';
        }

        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const index = Math.floor(Math.log(bytes) / Math.log(1024));
        const size = bytes / Math.pow(1024, index);

        return `${size.toFixed(index === 0 ? 0 : 2)} ${sizes[index]}`;
    }

    function setText(element, value) {
        if (element) {
            element.textContent = value;
        }
    }

    function showSelectedFile(elements, file) {
        if (!file) {
            return;
        }

        elements.dropArea.classList.add('is-selected');
        elements.dropArea.classList.remove('is-dragover');

        setText(elements.dropText, SELECTED_DROP_TEXT);
        setText(elements.dropHint, SELECTED_DROP_HINT);
        setText(elements.selectedFileName, file.name);
        setText(elements.selectedFileSize, formatFileSize(file.size));

        elements.selectedFileBox.hidden = false;
    }

    function resetSelectedFile(elements) {
        elements.fileInput.value = '';

        elements.dropArea.classList.remove('is-selected', 'is-dragover');

        setText(elements.dropText, DEFAULT_DROP_TEXT);
        setText(elements.dropHint, DEFAULT_DROP_HINT);
        setText(elements.selectedFileName, '-');
        setText(elements.selectedFileSize, '-');

        elements.selectedFileBox.hidden = true;
    }

    function assignDroppedFile(elements, file) {
        if (!file) {
            return;
        }

        const dataTransfer = new DataTransfer();

        dataTransfer.items.add(file);
        elements.fileInput.files = dataTransfer.files;

        showSelectedFile(elements, file);
    }

    function setProgress(elements, percent, statusText) {
        if (elements.uploadProgress) {
            elements.uploadProgress.hidden = false;
        }

        if (elements.uploadBar) {
            elements.uploadBar.style.width = `${Math.min(Math.max(percent, 0), 100)}%`;
        }

        if (elements.uploadStatus && statusText) {
            elements.uploadStatus.textContent = statusText;
        }
    }

    function startUploadProgress(elements) {
        let percent = 0;

        setProgress(elements, 0, 'Membaca file Excel/CSV...');

        if (uploadTimer) {
            clearInterval(uploadTimer);
        }

        uploadTimer = window.setInterval(function () {
            percent = Math.min(percent + Math.random() * 11, 92);

            let statusText = 'Mengirim data ke Flask API...';

            if (percent < 35) {
                statusText = 'Membaca file Excel/CSV...';
            } else if (percent < 65) {
                statusText = 'Memvalidasi NISN dan nilai siswa...';
            }

            setProgress(elements, percent.toFixed(0), statusText);

            if (percent >= 92) {
                clearInterval(uploadTimer);
                uploadTimer = null;
            }
        }, 220);
    }

    function disableSubmitButton(elements) {
        if (!elements.uploadButton) {
            return;
        }

        elements.uploadButton.disabled = true;
        elements.uploadButton.textContent = 'Memproses data...';
    }

    function hasSelectedFile(elements) {
        return elements.fileInput.files && elements.fileInput.files.length > 0;
    }

    function initUploadEvents() {
        const elements = getElements();

        if (!elements.dropArea || !elements.fileInput || !elements.formUpload) {
            return;
        }

        elements.dropArea.addEventListener('click', function (event) {
            if (event.target.closest('#removeFileBtn')) {
                return;
            }

            elements.fileInput.click();
        });

        elements.fileInput.addEventListener('change', function () {
            if (hasSelectedFile(elements)) {
                showSelectedFile(elements, elements.fileInput.files[0]);
            }
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            elements.dropArea.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();

                elements.dropArea.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            elements.dropArea.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();

                elements.dropArea.classList.remove('is-dragover');
            });
        });

        elements.dropArea.addEventListener('drop', function (event) {
            const files = event.dataTransfer.files;

            if (!files || files.length === 0) {
                return;
            }

            assignDroppedFile(elements, files[0]);
        });

        if (elements.removeFileButton) {
            elements.removeFileButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                resetSelectedFile(elements);
            });
        }

        elements.formUpload.addEventListener('submit', function (event) {
            if (!hasSelectedFile(elements)) {
                event.preventDefault();
                alert(EMPTY_FILE_MESSAGE);
                return;
            }

            startUploadProgress(elements);
            disableSubmitButton(elements);
        });
    }

    document.addEventListener('DOMContentLoaded', initUploadEvents);
})();