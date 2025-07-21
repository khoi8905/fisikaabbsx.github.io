document.addEventListener('DOMContentLoaded', () => {
    // Get references to navigation buttons and content sections
    const navMateri = document.getElementById('navMateri');
    const navLKPD = document.getElementById('navLKPD');
    const navAsesmen = document.getElementById('navAsesmen');

    const materiSection = document.getElementById('materiSection');
    const lkpdSection = document.getElementById('lkpdSection');
    const asesmenSection = document.getElementById('asesmenSection');

    const loginFormContainer = document.getElementById('loginFormContainer');
    const asesmenFormContainer = document.getElementById('asesmenFormContainer');
    const loginForm = document.getElementById('loginForm');
    const asesmenForm = document.getElementById('asesmenForm');
    const lkpdForm = document.getElementById('lkpdForm');
    const questionsContainer = document.getElementById('questionsContainer');

    let isAuthenticated = false; // To track login status

    // Function to show a specific section and hide others
    const showSection = (sectionToShow) => {
        [materiSection, lkpdSection, asesmenSection].forEach(section => {
            section.classList.add('hidden-section');
        });
        sectionToShow.classList.remove('hidden-section');
    };

    // Event listeners for navigation buttons
    navMateri.addEventListener('click', () => showSection(materiSection));
    navLKPD.addEventListener('click', () => showSection(lkpdSection));
    navAsesmen.addEventListener('click', () => {
        showSection(asesmenSection);
        if (isAuthenticated) {
            loginFormContainer.classList.add('hidden-section');
            asesmenFormContainer.classList.remove('hidden-section');
            loadQuestions(); // Load questions if already logged in
        } else {
            loginFormContainer.classList.remove('hidden-section');
            asesmenFormContainer.classList.add('hidden-section');
        }
    });

    // Initial display: show Materi Ajar section
    showSection(materiSection);

    // Handle Login Form Submission
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const username = loginForm.username.value;
        const password = loginForm.password.value;

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'login', username, password }),
            });

            const result = await response.json();

            if (result.success) {
                isAuthenticated = true;
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: 'Anda sekarang bisa mengerjakan asesmen.',
                    showConfirmButton: false,
                    timer: 1500
                });
                loginFormContainer.classList.add('hidden-section');
                asesmenFormContainer.classList.remove('hidden-section');
                loadQuestions(); // Load assessment questions
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    text: result.message || 'Username atau password salah.',
                });
            }
        } catch (error) {
            console.error('Error during login:', error);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Tidak dapat terhubung ke server. Silakan coba lagi.',
            });
        }
    });

    // Function to load assessment questions
    const loadQuestions = async () => {
        questionsContainer.innerHTML = '<p class="text-gray-600 text-center">Memuat soal asesmen...</p>';
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'get_questions' }),
            });
            const questions = await response.json();

            if (questions.success && questions.data) {
                questionsContainer.innerHTML = ''; // Clear loading message
                questions.data.forEach((q, index) => {
                    const questionDiv = document.createElement('div');
                    questionDiv.className = 'bg-white p-5 rounded-lg shadow-sm';
                    let questionHtml = `<p class="font-semibold text-gray-800 mb-3">Soal ${index + 1}. ${q.question}</p>`;

                    if (q.type === 'mcq') {
                        for (const optionKey in q.options) {
                            questionHtml += `
                                <div class="flex items-center mb-2">
                                    <input type="radio" id="q${q.id}-${optionKey}" name="q${q.id}" value="${optionKey}" class="mr-2 text-indigo-600 focus:ring-indigo-500">
                                    <label for="q${q.id}-${optionKey}" class="text-gray-700">${optionKey.toUpperCase()}. ${q.options[optionKey]}</label>
                                </div>
                            `;
                        }
                    } else if (q.type === 'essay') {
                        questionHtml += `
                            <textarea id="q${q.id}" name="q${q.id}" rows="4" placeholder="Tulis jawaban Anda di sini..." class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        `;
                    }
                    questionDiv.innerHTML = questionHtml;
                    questionsContainer.appendChild(questionDiv);
                });
            } else {
                questionsContainer.innerHTML = '<p class="text-red-600 text-center">Gagal memuat soal asesmen. ' + (questions.message || '') + '</p>';
            }
        } catch (error) {
            console.error('Error loading questions:', error);
            questionsContainer.innerHTML = '<p class="text-red-600 text-center">Terjadi kesalahan saat memuat soal.</p>';
        }
    };

    // Handle Asesmen Form Submission
    asesmenForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        Swal.fire({
            title: 'Konfirmasi Pengiriman',
            text: 'Apakah Anda yakin ingin mengirim jawaban asesmen ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const namaLengkap = asesmenForm.namaLengkap.value;
                const kelas = asesmenForm.kelas.value;
                const answers = {};

                // Collect answers for MCQs
                document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                    answers[input.name] = input.value;
                });

                // Collect answers for Essays
                document.querySelectorAll('textarea').forEach(textarea => {
                    answers[textarea.id] = textarea.value;
                });

                const submissionData = {
                    action: 'submit_answers',
                    namaLengkap,
                    kelas,
                    answers,
                    timestamp: new Date().toISOString()
                };

                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(submissionData),
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire(
                            'Terkirim!',
                            'Jawaban Anda telah berhasil dikirim.',
                            'success'
                        ).then(() => {
                            asesmenForm.reset(); // Clear the form
                            // Optionally, redirect or show a thank you message
                            showSection(materiSection); // Go back to materi after submission
                            isAuthenticated = false; // Reset authentication
                        });
                    } else {
                        Swal.fire(
                            'Gagal!',
                            result.message || 'Terjadi kesalahan saat mengirim jawaban.',
                            'error'
                        );
                    }
                } catch (error) {
                    console.error('Error submitting answers:', error);
                    Swal.fire(
                        'Error!',
                        'Tidak dapat terhubung ke server. Silakan coba lagi.',
                        'error'
                    );
                }
            }
        });
    });

    // Handle LKPD Form Submission
    lkpdForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(lkpdForm);
        formData.append('action', 'submit_lkpd');

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData, // FormData handles multipart/form-data automatically
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Hasil LKPD Anda telah berhasil diunggah.',
                    showConfirmButton: false,
                    timer: 2000
                });
                lkpdForm.reset(); // Clear the form
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengunggah!',
                    text: result.message || 'Terjadi kesalahan saat mengunggah file.',
                });
            }
        } catch (error) {
            console.error('Error submitting LKPD:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Tidak dapat terhubung ke server. Silakan coba lagi.',
            });
        }
    });
});
