/**
 * CBT School - Exam Taking Application (Partial-Based Architecture)
 * Works with server-rendered question partials via @include
 * Handles timer, navigation, auto-save, fullscreen, submission, and answer collection
 */

class ExamApp {
    constructor(config) {
        this.config = {
            examId: config.examId,
            participantId: config.participantId,
            durationMinutes: config.durationMinutes,
            totalQuestions: config.totalQuestions,
            shuffleQuestions: config.shuffleQuestions === 'true',
            shuffleOptions: config.shuffleOptions === 'true',
            apiBase: config.apiBase,
            questions: config.questions || []
        };

        // State
        this.questions = [...this.config.questions];
        this.currentIndex = 0;
        this.answers = new Map(); // questionId -> answer value
        this.flagged = new Set(); // questionId
        this.startTime = null;
        this.endTime = null;
        this.timerInterval = null;
        this.autoSaveInterval = null;
        this.syncInterval = null;
        this.debounceTimer = null;
        this.warningCount = 0;
        this.maxWarnings = 3;
        this.isFullscreen = false;
        this.isSubmitting = false;
        this.lastSaved = null;
        this.connectionStatus = 'connected';
        this.lastServerTime = null;
        this.timeOffset = 0;
        this.fullscreenExited = false;

        // DOM Elements
        this.elements = {
            timerDisplay: document.getElementById('timerDisplay'),
            connectionStatus: document.getElementById('connectionStatus'),
            connectionText: document.getElementById('connectionText'),
            currentQuestionNum: document.getElementById('currentQuestionNum'),
            totalQuestions: document.getElementById('totalQuestions'),
            questionPoints: document.getElementById('questionPoints'),
            questionType: document.getElementById('questionType'),
            flagBtn: document.getElementById('flagBtn'),
            prevBtn: document.getElementById('prevBtn'),
            nextBtn: document.getElementById('nextBtn'),
            submitBtn: document.getElementById('submitBtn'),
            paletteToggleBtn: document.getElementById('paletteToggleBtn'),
            paletteCloseBtn: document.getElementById('paletteCloseBtn'),
            paletteToggleMobile: document.getElementById('paletteToggleMobile'),
            questionPalette: document.getElementById('questionPalette'),
            paletteList: document.getElementById('paletteList'),
            statAnswered: document.getElementById('statAnswered'),
            statFlagged: document.getElementById('statFlagged'),
            statUnanswered: document.getElementById('statUnanswered'),
            autoSaveStatus: document.getElementById('autoSaveStatus'),
            fullscreenWarning: document.getElementById('fullscreenWarning'),
            warningCount: document.getElementById('warningCount'),
            warningNumber: document.getElementById('warningNumber'),
            submitModal: document.getElementById('submitModal'),
            cancelSubmitBtn: document.getElementById('cancelSubmitBtn'),
            confirmSubmitBtn: document.getElementById('confirmSubmitBtn'),
            modalAnswered: document.getElementById('modalAnswered'),
            modalUnanswered: document.getElementById('modalUnanswered'),
            modalFlagged: document.getElementById('modalFlagged'),
            timeUpModal: document.getElementById('timeUpModal'),
            timeUpOkBtn: document.getElementById('timeUpOkBtn'),
            enterFullscreenBtn: document.getElementById('enterFullscreenBtn'),
            goToFirstUnansweredBtn: document.getElementById('goToFirstUnansweredBtn')
        };

        // Initialize from server-rendered answers
        this.collectInitialAnswers();

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadSavedState();
        this.startExam();
        this.startTimers();
        this.setupFullscreen();
        this.renderPalette();
        this.showQuestion(this.currentIndex);
        this.updateNavigation();
        this.updateSubmitButton();
        this.updatePaletteStats();
    }

    setupEventListeners() {
        // Navigation
        this.elements.prevBtn.addEventListener('click', () => this.navigate(-1));
        this.elements.nextBtn.addEventListener('click', () => this.navigate(1));
        this.elements.submitBtn.addEventListener('click', () => this.showSubmitModal());
        this.elements.cancelSubmitBtn.addEventListener('click', () => this.hideSubmitModal());
        this.elements.confirmSubmitBtn.addEventListener('click', () => this.submitExam());

        // Palette
        this.elements.paletteToggleBtn.addEventListener('click', () => this.togglePalette());
        this.elements.paletteCloseBtn.addEventListener('click', () => this.closePalette());
        this.elements.paletteToggleMobile.addEventListener('click', () => this.togglePalette());
        this.elements.goToFirstUnansweredBtn.addEventListener('click', () => this.goToFirstUnanswered());

        // Flag
        this.elements.flagBtn.addEventListener('click', () => this.toggleFlag());

        // Fullscreen warning
        this.elements.enterFullscreenBtn.addEventListener('click', () => this.requestFullscreen());

        // Time up modal
        this.elements.timeUpOkBtn.addEventListener('click', () => this.submitExam());

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Visibility change (tab switch, minimize)
        document.addEventListener('visibilitychange', () => this.handleVisibilityChange());

        // Before unload
        window.addEventListener('beforeunload', (e) => this.handleBeforeUnload(e));

        // Online/offline
        window.addEventListener('online', () => this.setConnectionStatus('connected'));
        window.addEventListener('offline', () => this.setConnectionStatus('disconnected'));

        // Custom events from partials
        window.addEventListener('exam:answer-changed', (e) => this.handlePartialAnswerChange(e));
        window.addEventListener('exam:flag-toggled', (e) => this.handlePartialFlagToggle(e));

        // Drag and drop for matching/ordering (delegated)
        this.setupDragAndDrop();
    }

    // ==================== Answer Collection (from server-rendered partials) ====================

    collectInitialAnswers() {
        this.questions.forEach(q => {
            const answer = this.collectAnswerFromDOM(q.id);
            if (answer !== null && answer !== '' && !(Array.isArray(answer) && answer.length === 0) && !(typeof answer === 'object' && Object.keys(answer).length === 0)) {
                this.answers.set(q.id, answer);
            }
        });
    }

    collectAnswerFromDOM(questionId) {
        const page = document.querySelector(`.question-page[data-question-id="${questionId}"]`);
        if (!page) return null;

        const q = this.questions.find(q => String(q.id) === String(questionId));
        if (!q) return null;

        switch (q.type) {
            case 'mcq':
            case 'truefalse': {
                const checked = page.querySelector('input[type="radio"][name^="answers["]:checked');
                return checked ? { option_id: checked.value } : null;
            }
            case 'multiresponse': {
                const checked = page.querySelectorAll('input[type="checkbox"][name^="answers["]:checked');
                return Array.from(checked).map(c => c.value).length > 0 ? { option_ids: Array.from(checked).map(c => c.value) } : null;
            }
            case 'essay': {
                const textarea = page.querySelector('textarea[name^="answers["]');
                if (!textarea) return null;
                const text = textarea.value;
                const wordCount = text.trim() ? text.trim().split(/\s+/).length : 0;
                return { text, word_count: wordCount, char_count: text.length };
            }
            case 'fillblank': {
                // Collect all blanks: inputs with name="answers[questionId][blanks][blankNum]"
                const inputs = page.querySelectorAll('input[name^="answers["][name$="blanks]"]');
                const blanks = {};
                inputs.forEach(input => {
                    const match = input.name.match(/\[blanks\]\[(\d+)\]/);
                    if (match && input.value.trim()) {
                        blanks[match[1]] = input.value.trim();
                    }
                });
                return Object.keys(blanks).length > 0 ? { blanks } : null;
            }
            case 'matching': {
                // Collect from select elements: name="answers[questionId][matches][leftId]"
                const selects = page.querySelectorAll('select[name^="answers["][name$="matches]"]');
                const matches = {};
                selects.forEach(select => {
                    const match = select.name.match(/\[matches\]\[([^\]]+)\]/);
                    if (match && select.value) {
                        matches[match[1]] = select.value;
                    }
                });
                return Object.keys(matches).length > 0 ? { matches } : null;
            }
            case 'ordering': {
                // Collect from hidden inputs: name="answers[questionId][order][itemId]"
                const inputs = page.querySelectorAll('input[type="hidden"][name^="answers["][name$="order]"]');
                const order = {};
                inputs.forEach(input => {
                    const match = input.name.match(/\[order\]\[([^\]]+)\]/);
                    if (match && input.value) {
                        order[match[1]] = parseInt(input.value, 10);
                    }
                });
                // Return as array of itemIds in position order (1,2,3...)
                if (Object.keys(order).length > 0) {
                    const sorted = Object.entries(order).sort((a, b) => a[1] - b[1]);
                    return { order: sorted.map(([itemId]) => itemId) };
                }
                return null;
            }
            default:
                return null;
        }
    }

    collectCurrentAnswer() {
        const question = this.getCurrentQuestion();
        if (!question) return;
        const answer = this.collectAnswerFromDOM(question.id);
        if (answer !== null && answer !== '' && !(Array.isArray(answer) && answer.length === 0) && !(typeof answer === 'object' && Object.keys(answer).length === 0)) {
            this.answers.set(question.id, answer);
        } else {
            this.answers.delete(question.id);
        }
    }

    handlePartialAnswerChange(e) {
        const { questionId, value } = e.detail;
        if (value !== null && value !== undefined) {
            this.answers.set(questionId, value);
        } else {
            this.answers.delete(questionId);
        }
        this.updatePaletteStats();
        this.updatePaletteHighlight();
        this.updateSubmitButton();
        this.debouncedAutoSave();
    }

    handlePartialFlagToggle(e) {
        const { questionId, flagged } = e.detail;
        if (flagged) {
            this.flagged.add(questionId);
        } else {
            this.flagged.delete(questionId);
        }
        this.updatePaletteStats();
        this.updatePaletteHighlight();
        this.updateFlagButton();
    }

    debouncedAutoSave() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.autoSave(), 2000);
    }

    // ==================== Exam Lifecycle ====================

    startExam() {
        this.startTime = Date.now();
        this.endTime = this.startTime + (this.config.durationMinutes * 60 * 1000);

        this.syncTime();

        this.api('exam/start', { participant_id: this.config.participantId }, 'POST')
            .then(res => {
                if (res.server_time) {
                    this.lastServerTime = res.server_time;
                    this.timeOffset = this.lastServerTime - Date.now();
                    this.endTime = this.lastServerTime + (this.config.durationMinutes * 60 * 1000);
                }
            })
            .catch(err => console.error('Failed to start exam:', err));
    }

    async syncTime() {
        try {
            const res = await this.api('time');
            if (res.server_time) {
                this.lastServerTime = res.server_time;
                this.timeOffset = this.lastServerTime - Date.now();
            }
        } catch (e) {
            console.warn('Time sync failed:', e);
        }
    }

    getServerTime() {
        return Date.now() + this.timeOffset;
    }

    // ==================== Timer ====================

    startTimers() {
        this.timerInterval = setInterval(() => this.updateTimer(), 1000);
        this.updateTimer();

        this.autoSaveInterval = setInterval(() => this.autoSave(), 30000);
        this.syncInterval = setInterval(() => this.syncTime(), 30000);
    }

    updateTimer() {
        const now = this.getServerTime();
        const remaining = this.endTime - now;

        if (remaining <= 0) {
            this.timeUp();
            return;
        }

        const hours = Math.floor(remaining / 3600000);
        const minutes = Math.floor((remaining % 3600000) / 60000);
        const seconds = Math.floor((remaining % 60000) / 1000);

        const display = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        this.elements.timerDisplay.textContent = display;
        this.elements.timerDisplay.setAttribute('datetime', `PT${hours}H${minutes}M${seconds}S`);

        this.elements.timerDisplay.classList.remove('warning', 'danger');
        if (remaining <= 60000) {
            this.elements.timerDisplay.classList.add('danger');
        } else if (remaining <= 300000) {
            this.elements.timerDisplay.classList.add('warning');
        }

        // Progress bar
        const progress = Math.max(0, (remaining / (this.config.durationMinutes * 60 * 1000)) * 100);
        const progressBar = document.getElementById('timerProgressBar');
        if (progressBar) progressBar.style.width = `${progress}%`;
    }

    timeUp() {
        clearInterval(this.timerInterval);
        this.elements.timerDisplay.textContent = '00:00:00';
        this.elements.timerDisplay.classList.add('danger');
        this.autoSave(true);
        this.showTimeUpModal();
    }

    // ==================== Question Navigation (Show/Hide Pre-rendered Pages) ====================

    getCurrentQuestion() {
        return this.questions[this.currentIndex] || null;
    }

    showQuestion(index) {
        if (index < 0 || index >= this.questions.length) return;

        // Collect answer from current page before switching
        this.collectCurrentAnswer();

        // Hide all pages
        document.querySelectorAll('.question-page').forEach(page => page.hidden = true);

        // Show target page
        const targetPage = document.querySelector(`.question-page[data-question-index="${index}"]`);
        if (targetPage) {
            targetPage.hidden = false;
        }

        this.currentIndex = index;
        const question = this.getCurrentQuestion();

        if (question) {
            this.elements.currentQuestionNum.textContent = index + 1;
            this.elements.questionPoints.textContent = `${question.points || 1} poin`;
            this.elements.questionType.textContent = this.getQuestionTypeLabel(question.type);

            // Update flag button
            this.updateFlagButton();

            // Restore answer in UI (for essay/fillblank - radio/checkbox are already checked from server render)
            this.restoreAnswerInDOM(question);

            // Scroll to top of question area
            const qArea = document.querySelector('.exam-question-area');
            if (qArea) qArea.scrollTop = 0;
        }

        this.updateNavigation();
        this.updatePaletteStats();
        this.updatePaletteHighlight();
    }

    updateFlagButton() {
        const question = this.getCurrentQuestion();
        if (!question) return;
        const isFlagged = this.flagged.has(question.id);
        this.elements.flagBtn.classList.toggle('active', isFlagged);
        const flagLabel = this.elements.flagBtn.querySelector('span');
        if (flagLabel) flagLabel.textContent = isFlagged ? 'Batalkan Tanda' : 'Tandai';
        this.elements.flagBtn.setAttribute('aria-pressed', isFlagged);
    }

    restoreAnswerInDOM(question) {
        const answer = this.answers.get(question.id);
        if (answer === null || answer === undefined) return;

        const page = document.querySelector(`.question-page[data-question-id="${question.id}"]`);
        if (!page) return;

        switch (question.type) {
            case 'essay': {
                const textarea = page.querySelector('textarea[name^="answers["]');
                if (textarea && answer.text !== '') {
                    textarea.value = answer.text;
                    textarea.classList.add('has-content');
                    // Trigger input event to update counters
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                }
                break;
            }
            case 'fillblank': {
                // Restore all blanks from answer.blanks
                if (answer.blanks) {
                    Object.entries(answer.blanks).forEach(([blankNum, value]) => {
                        const input = page.querySelector(`input[name="answers[${question.id}][blanks][${blankNum}]"]`);
                        if (input && value !== '') {
                            input.value = value;
                            input.classList.add('fillblank-render__input--filled', 'has-content');
                        }
                    });
                }
                break;
            }
            case 'matching': {
                // Restore select values from answer.matches
                if (answer.matches) {
                    Object.entries(answer.matches).forEach(([leftId, rightId]) => {
                        const select = page.querySelector(`select[name="answers[${question.id}][matches][${leftId}]"]`);
                        if (select) {
                            select.value = rightId;
                        }
                    });
                }
                break;
            }
            case 'ordering': {
                // Restore hidden input values from answer.order (array of itemIds in position order)
                if (answer.order && Array.isArray(answer.order)) {
                    answer.order.forEach((itemId, position) => {
                        const input = page.querySelector(`input[type="hidden"][name="answers[${question.id}][order][${itemId}]"]`);
                        if (input) {
                            input.value = position + 1;
                        }
                    });
                }
                break;
            }
        }
    }

    restoreMatchingDragState(question, answer) {
        const page = document.querySelector(`.question-page[data-question-id="${question.id}"]`);
        if (!page || !answer || !answer.pairs) return;

        const rightItems = page.querySelectorAll('.matching-item[data-right-id]');
        const matchedRightIds = new Set(Object.values(answer.pairs));

        rightItems.forEach(item => {
            if (matchedRightIds.has(item.dataset.rightId)) {
                item.classList.add('matched');
                item.setAttribute('aria-grabbed', 'true');
            }
        });
    }

    navigate(direction) {
        const newIndex = this.currentIndex + direction;
        if (newIndex >= 0 && newIndex < this.questions.length) {
            this.showQuestion(newIndex);
        }
    }

    goToFirstUnanswered() {
        for (let i = 0; i < this.questions.length; i++) {
            const q = this.questions[i];
            const answer = this.answers.get(q.id);
            if (!answer || (Array.isArray(answer) && answer.length === 0) || (typeof answer === 'string' && answer.trim() === '') || (typeof answer === 'object' && Object.keys(answer).length === 0)) {
                this.showQuestion(i);
                this.openPalette();
                return;
            }
        }
    }

    updateNavigation() {
        this.elements.prevBtn.disabled = this.currentIndex === 0;
        this.elements.nextBtn.disabled = this.currentIndex === this.questions.length - 1;
    }

    // ==================== Flagging ====================

    toggleFlag() {
        const question = this.getCurrentQuestion();
        if (!question) return;

        if (this.flagged.has(question.id)) {
            this.flagged.delete(question.id);
        } else {
            this.flagged.add(question.id);
        }

        this.updateFlagButton();
        this.updatePaletteStats();
        this.updatePaletteHighlight();

        // Also update the flag button in the question card
        const card = document.querySelector(`.question-card[data-question-id="${question.id}"]`);
        const flagBtn = card?.querySelector('.question-flag');
        if (flagBtn) {
            flagBtn.setAttribute('aria-pressed', this.flagged.has(question.id));
            card.classList.toggle('question-card--flagged', this.flagged.has(question.id));
        }

        // Notify server
        this.api('exam/flag', { question_id: question.id, flagged: this.flagged.has(question.id) }, 'POST').catch(console.error);
    }

    // ==================== Palette ====================

    renderPalette() {
        const list = this.elements.paletteList;
        if (!list) return;

        list.innerHTML = this.questions.map((q, i) => `
            <li class="palette-item
                ${this.answers.has(q.id) ? ' palette-item--answered' : ''}
                ${this.flagged.has(q.id) ? ' palette-item--flagged' : ''}
                ${i === this.currentIndex ? ' active' : ''}
            " data-question-index="${i}" data-question-id="${this.escapeHtml(q.id)}" role="option" tabindex="0" aria-label="Soal ${i + 1}${this.flagged.has(q.id) ? ', ditandai' : ''}${this.answers.has(q.id) ? ', dijawab' : ', belum dijawab'}">
                <span class="palette-item__number">${i + 1}</span>
                <span class="palette-item__status" aria-hidden="true"></span>
            </li>
        `).join('');

        list.querySelectorAll('.palette-item').forEach(item => {
            item.addEventListener('click', () => {
                const idx = parseInt(item.dataset.questionIndex, 10);
                this.showQuestion(idx);
                this.closePalette();
            });
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });
    }

    updatePaletteHighlight() {
        this.elements.paletteList.querySelectorAll('.palette-item').forEach(item => {
            const idx = parseInt(item.dataset.questionIndex, 10);
            item.classList.toggle('active', idx === this.currentIndex);
        });
    }

    updatePaletteStats() {
        let answered = 0, flagged = 0;
        this.questions.forEach(q => {
            const ans = this.answers.get(q.id);
            if (ans && !(Array.isArray(ans) && ans.length === 0) && ans !== '' && !(typeof ans === 'object' && Object.keys(ans).length === 0)) {
                answered++;
            }
            if (this.flagged.has(q.id)) flagged++;
        });
        const unanswered = this.questions.length - answered;

        if (this.elements.statAnswered) this.elements.statAnswered.textContent = answered;
        if (this.elements.statFlagged) this.elements.statFlagged.textContent = flagged;
        if (this.elements.statUnanswered) this.elements.statUnanswered.textContent = unanswered;

        // Update palette items
        this.questions.forEach((q, i) => {
            const item = this.elements.paletteList.querySelector(`[data-question-index="${i}"]`);
            if (item) {
                const hasAnswer = this.answers.has(q.id) && this.answers.get(q.id);
                const isFlagged = this.flagged.has(q.id);
                item.classList.toggle('palette-item--answered', !!hasAnswer);
                item.classList.toggle('palette-item--flagged', isFlagged);
                item.setAttribute('aria-label', `Soal ${i + 1}${isFlagged ? ', ditandai' : ''}${hasAnswer ? ', dijawab' : ', belum dijawab'}`);
            }
        });

        // Update mobile badge
        const badge = document.querySelector('.palette-toggle-mobile__badge');
        if (badge) {
            badge.textContent = unanswered > 0 ? unanswered : '';
            badge.style.display = unanswered > 0 ? 'flex' : 'none';
        }
    }

    togglePalette() {
        const palette = this.elements.questionPalette;
        const isOpen = palette.classList.toggle('open');
        this.elements.paletteToggleBtn.setAttribute('aria-expanded', isOpen);
        this.elements.paletteToggleMobile.setAttribute('aria-expanded', isOpen);
    }

    openPalette() {
        this.elements.questionPalette.classList.add('open');
        this.elements.paletteToggleBtn.setAttribute('aria-expanded', 'true');
        this.elements.paletteToggleMobile.setAttribute('aria-expanded', 'true');
    }

    closePalette() {
        this.elements.questionPalette.classList.remove('open');
        this.elements.paletteToggleBtn.setAttribute('aria-expanded', 'false');
        this.elements.paletteToggleMobile.setAttribute('aria-expanded', 'false');
    }

    // ==================== Auto-save ====================

    async autoSave(force = false) {
        if (this.isSubmitting) return;

        // Collect current answer
        this.collectCurrentAnswer();

        const payload = {
            participant_id: this.config.participantId,
            answers: Object.fromEntries(this.answers),
            flagged: Array.from(this.flagged),
            current_question_index: this.currentIndex,
            elapsed_seconds: Math.floor((this.getServerTime() - this.startTime) / 1000)
        };

        try {
            this.setAutoSaveStatus('saving');
            const res = await this.api('exam/autosave', payload, 'POST');
            this.lastSaved = new Date();
            this.setAutoSaveStatus('saved');
            if (res.server_time) {
                this.lastServerTime = res.server_time;
                this.timeOffset = this.lastServerTime - Date.now();
            }
        } catch (err) {
            console.error('Auto-save failed:', err);
            this.setAutoSaveStatus('error');
        }
    }

    setAutoSaveStatus(status) {
        const el = this.elements.autoSaveStatus;
        if (!el) return;
        el.textContent = status === 'saving' ? 'Menyimpan...' : status === 'saved' ? 'Tersimpan' : 'Gagal menyimpan';
        el.className = `autosave-status autosave-status--${status}`;
        if (status === 'saved') {
            setTimeout(() => { el.textContent = 'Tersimpan'; el.className = 'autosave-status'; }, 2000);
        }
    }

    // ==================== Submission ====================

    showSubmitModal() {
        this.collectCurrentAnswer();
        this.updateSubmitModal();
        this.elements.submitModal.hidden = false;
        document.body.style.overflow = 'hidden';
        this.elements.cancelSubmitBtn.focus();
    }

    hideSubmitModal() {
        this.elements.submitModal.hidden = true;
        document.body.style.overflow = '';
    }

    updateSubmitModal() {
        let answered = 0, flagged = 0;
        this.questions.forEach(q => {
            const ans = this.answers.get(q.id);
            if (ans && !(Array.isArray(ans) && ans.length === 0) && ans !== '' && !(typeof ans === 'object' && Object.keys(ans).length === 0)) {
                answered++;
            }
            if (this.flagged.has(q.id)) flagged++;
        });
        const unanswered = this.questions.length - answered;

        if (this.elements.modalAnswered) this.elements.modalAnswered.textContent = answered;
        if (this.elements.modalUnanswered) this.elements.modalUnanswered.textContent = unanswered;
        if (this.elements.modalFlagged) this.elements.modalFlagged.textContent = flagged;
    }

    async submitExam() {
        if (this.isSubmitting) return;
        this.isSubmitting = true;

        this.collectCurrentAnswer();
        this.hideSubmitModal();
        this.elements.timeUpModal.hidden = true;

        const payload = {
            participant_id: this.config.participantId,
            answers: Object.fromEntries(this.answers),
            flagged: Array.from(this.flagged),
            elapsed_seconds: Math.floor((this.getServerTime() - this.startTime) / 1000),
            submitted_at: new Date().toISOString()
        };

        try {
            this.setConnectionStatus('submitting');
            const res = await this.api('exam/submit', payload, 'POST');
            // Redirect to result page
            window.location.href = `${this.config.apiBase.replace('/api', '')}/student/exams/${this.config.examId}/result?participant=${this.config.participantId}`;
        } catch (err) {
            console.error('Submit failed:', err);
            this.isSubmitting = false;
            this.setConnectionStatus('connected');
            alert('Gagal mengumpulkan ujian. Silakan coba lagi.');
        }
    }

    showTimeUpModal() {
        this.elements.timeUpModal.hidden = false;
    }

    // ==================== Fullscreen ====================

    setupFullscreen() {
        // Check if we should enforce fullscreen
        const enforceFullscreen = document.body.dataset.enforceFullscreen === 'true';
        if (enforceFullscreen && !this.isFullscreen) {
            this.showFullscreenOverlay();
        }

        // Listen for fullscreen changes
        document.addEventListener('fullscreenchange', () => this.handleFullscreenChange());
    }

    showFullscreenOverlay() {
        const overlay = document.getElementById('fullscreenOverlay');
        if (overlay) overlay.hidden = false;
    }

    async requestFullscreen() {
        try {
            await document.documentElement.requestFullscreen();
            this.isFullscreen = true;
            document.body.classList.add('exam-fullscreen');
            const overlay = document.getElementById('fullscreenOverlay');
            if (overlay) overlay.hidden = true;
        } catch (err) {
            console.error('Fullscreen request failed:', err);
        }
    }

    handleFullscreenChange() {
        if (document.fullscreenElement) {
            this.isFullscreen = true;
            document.body.classList.add('exam-fullscreen');
            const warning = document.getElementById('fullscreenWarning');
            if (warning) warning.hidden = true;
        } else {
            this.isFullscreen = false;
            document.body.classList.remove('exam-fullscreen');
            if (this.fullscreenExited) return; // Already handled
            this.handleFullscreenExit();
        }
    }

    handleFullscreenExit() {
        this.warningCount++;
        this.fullscreenExited = true;

        const warning = document.getElementById('fullscreenWarning');
        const countEl = document.getElementById('warningCount');
        if (warning && countEl) {
            countEl.textContent = this.warningCount;
            warning.hidden = false;
        }

        if (this.warningCount >= this.maxWarnings) {
            this.autoSave(true);
            setTimeout(() => this.submitExam(), 2000);
        } else {
            // Auto-request fullscreen again after 3 seconds
            setTimeout(() => {
                this.fullscreenExited = false;
                this.requestFullscreen();
            }, 3000);
        }
    }

    // ==================== Connection & Visibility ====================

    setConnectionStatus(status) {
        this.connectionStatus = status;
        const el = this.elements.connectionStatus;
        const textEl = this.elements.connectionText;
        if (!el || !textEl) return;

        el.className = 'connection-status';
        if (status === 'connected') {
            textEl.textContent = 'Terhubung';
            el.classList.remove('connection-status--connecting', 'connection-status--disconnected');
        } else if (status === 'connecting') {
            textEl.textContent = 'Menyambungkan...';
            el.classList.add('connection-status--connecting');
        } else if (status === 'disconnected') {
            textEl.textContent = 'Terputus';
            el.classList.add('connection-status--disconnected');
        } else if (status === 'submitting') {
            textEl.textContent = 'Mengumpulkan...';
            el.classList.add('connection-status--connecting');
        }
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.autoSave();
        } else {
            // Page became visible again - sync time
            this.syncTime();
        }
    }

    handleBeforeUnload(e) {
        if (!this.isSubmitting && this.answers.size > 0) {
            this.autoSave(true);
            e.preventDefault();
            e.returnValue = '';
        }
    }

    // ==================== Keyboard Shortcuts ====================

    handleKeydown(e) {
        // Don't intercept if typing in input/textarea
        const active = document.activeElement;
        const isInput = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable);
        if (isInput && !(e.altKey && e.key.toLowerCase() === 'f')) return;

        switch (e.key) {
            case 'ArrowLeft':
                if (e.altKey || e.ctrlKey) {
                    e.preventDefault();
                    this.navigate(-1);
                }
                break;
            case 'ArrowRight':
                if (e.altKey || e.ctrlKey) {
                    e.preventDefault();
                    this.navigate(1);
                }
                break;
            case 'f':
            case 'F':
                if (e.altKey) {
                    e.preventDefault();
                    this.toggleFlag();
                }
                break;
            case 'p':
            case 'P':
                if (e.altKey) {
                    e.preventDefault();
                    this.togglePalette();
                }
                break;
            case 'Enter':
                if (e.ctrlKey && e.shiftKey) {
                    e.preventDefault();
                    this.showSubmitModal();
                }
                break;
        }
    }

    // ==================== Drag & Drop (Matching/Ordering) ====================

    setupDragAndDrop() {
        // Delegated drag events for matching
        document.addEventListener('dragstart', (e) => {
            const item = e.target.closest('.matching-item[draggable="true"], .ordering-item[draggable="true"]');
            if (item) {
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.leftId || item.dataset.itemIndex || '');
            }
        });

        document.addEventListener('dragend', (e) => {
            const item = e.target.closest('.matching-item, .ordering-item');
            if (item) item.classList.remove('dragging');
        });

        document.addEventListener('dragover', (e) => {
            const dropzone = e.target.closest('.matching-dropzone, .ordering-dropzone');
            if (dropzone) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                dropzone.classList.add('drag-over');
            }
        });

        document.addEventListener('dragleave', (e) => {
            const dropzone = e.target.closest('.matching-dropzone, .ordering-dropzone');
            if (dropzone && !dropzone.contains(e.relatedTarget)) {
                dropzone.classList.remove('drag-over');
            }
        });

        document.addEventListener('drop', (e) => {
            const dropzone = e.target.closest('.matching-dropzone, .ordering-dropzone');
            if (!dropzone) return;

            e.preventDefault();
            dropzone.classList.remove('drag-over');

            const draggedId = e.dataTransfer.getData('text/plain');
            const draggedItem = document.querySelector(`[draggable="true"][data-left-id="${draggedId}"], [draggable="true"][data-item-index="${draggedId}"]`);
            if (!draggedItem) return;

            const questionPage = dropzone.closest('.question-page');
            const questionId = questionPage?.dataset.questionId;
            if (!questionId) return;

            if (dropzone.classList.contains('matching-dropzone')) {
                this.handleMatchingDrop(questionId, draggedId, dropzone.dataset.rightId);
            } else if (dropzone.classList.contains('ordering-dropzone')) {
                this.handleOrderingDrop(questionId, draggedItem, dropzone);
            }
        });
    }

    handleMatchingDrop(questionId, leftId, rightId) {
        const page = document.querySelector(`.question-page[data-question-id="${questionId}"]`);
        if (!page) return;

        // Update visual state
        const leftItem = page.querySelector(`.matching-item[data-left-id="${leftId}"]`);
        const rightItem = page.querySelector(`.matching-item[data-right-id="${rightId}"]`);
        const dropzone = page.querySelector(`.matching-dropzone[data-right-id="${rightId}"]`);

        if (leftItem && rightItem && dropzone) {
            // Move left item to dropzone
            dropzone.appendChild(leftItem);
            leftItem.classList.add('matched');
            leftItem.setAttribute('aria-grabbed', 'true');
            rightItem.classList.add('matched');

            // Collect updated pairs
            const pairs = {};
            page.querySelectorAll('.matching-pair[data-left-id]').forEach(pair => {
                const left = pair.querySelector('.matching-item[data-left-id]');
                const rightId = pair.dataset.rightId;
                if (left) pairs[left.dataset.leftId] = rightId;
            });

            this.answers.set(questionId, { pairs });
            this.updatePaletteStats();
            this.debouncedAutoSave();
        }
    }

    handleOrderingDrop(questionId, draggedItem, dropzone) {
        const page = document.querySelector(`.question-page[data-question-id="${questionId}"]`);
        if (!page) return;

        const container = dropzone.parentElement;
        const items = Array.from(container.querySelectorAll('.ordering-item'));

        // Re-insert dragged item at dropzone position
        container.insertBefore(draggedItem, dropzone.nextSibling);

        // Update indices
        items.forEach((item, idx) => {
            item.dataset.itemIndex = idx;
            const numEl = item.querySelector('.ordering-item__number');
            if (numEl) numEl.textContent = idx + 1;
        });

        // Collect updated order
        const order = items.map(item => parseInt(item.dataset.itemIndex, 10));
        this.answers.set(questionId, { order });
        this.updatePaletteStats();
        this.debouncedAutoSave();
    }

    // ==================== Helpers ====================

    getQuestionTypeLabel(type) {
        const labels = {
            mcq: 'Pilihan Ganda',
            multiresponse: 'Jawaban Berganda',
            truefalse: 'Benar/Salah',
            essay: 'Essay',
            fillblank: 'Isian',
            matching: 'Pencocokan',
            ordering: 'Pengurutan'
        };
        return labels[type] || type;
    }

    escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, c => ({
            '&': '&',
            '<': '<',
            '>': '>',
            '"': '"',
            "'": "'"
        }[c]));
    }

    // ==================== API ====================

    async api(endpoint, data = null, method = 'GET') {
        const url = `${this.config.apiBase}/${endpoint}`;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        };
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const res = await fetch(url, options);
        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: `HTTP ${res.status}` }));
            throw new Error(err.message || `Request failed: ${res.status}`);
        }
        return res.json();
    }

    // ==================== State Persistence ====================

    loadSavedState() {
        try {
            const saved = sessionStorage.getItem(`exam_${this.config.examId}_${this.config.participantId}`);
            if (saved) {
                const state = JSON.parse(saved);
                if (state.answers) {
                    Object.entries(state.answers).forEach(([k, v]) => this.answers.set(k, v));
                }
                if (state.flagged) {
                    state.flagged.forEach(id => this.flagged.add(id));
                }
                if (state.currentIndex !== undefined) {
                    this.currentIndex = state.currentIndex;
                }
            }
        } catch (e) {
            console.warn('Failed to load saved state:', e);
        }
    }

    saveState() {
        try {
            const state = {
                answers: Object.fromEntries(this.answers),
                flagged: Array.from(this.flagged),
                currentIndex: this.currentIndex,
                timestamp: Date.now()
            };
            sessionStorage.setItem(`exam_${this.config.examId}_${this.config.participantId}`, JSON.stringify(state));
        } catch (e) {
            console.warn('Failed to save state:', e);
        }
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ExamApp;
}
