window.attendanceBiometric = function ({ workType, officeLocations, credentialId, webauthnChallenge }) {
    return {
        gpsStatus: 'loading',
        geofenceOk: false,
        latitude: null,
        longitude: null,
        address: '',
        timezone: (() => {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            return ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'].includes(tz) ? tz : 'Asia/Jakarta';
        })(),

        biometricStatus: credentialId ? 'idle' : 'no-credential',
        hasCredential: credentialId !== null,
        password: '',
        showPassword: false,
        processing: false,
        workType,

        get biometricLabel() {
            const ua = navigator.userAgent;
            if (/Android/i.test(ua)) return 'Sidik Jari';
            if (/iPhone|iPad/i.test(ua)) return 'Face ID';
            return 'Biometrik';
        },

        get needsGeofence() {
            return this.workType === 'wfo';
        },

        setWorkType(wt) {
            this.workType = wt;
            this.checkGeofence();
        },

        get gpsStatusText() {
            if (this.gpsStatus === 'loading') return 'Mendapatkan lokasi…';
            if (this.gpsStatus === 'error') return 'Gagal mendapatkan lokasi';
            if (!this.needsGeofence) return 'Lokasi diperoleh';
            return this.geofenceOk ? 'Dalam radius kantor' : 'Di luar radius';
        },

        get canProceed() {
            // WFO requires GPS to be resolved and within radius.
            // WFA/Hybrid-WFA never needs GPS so we never block on it.
            const locationOk = !this.needsGeofence || (this.gpsStatus === 'ok' && this.geofenceOk);
            const authOk = this.hasCredential || this.password.length > 0;
            return locationOk && authOk;
        },

        init() {
            this.getLocation();
        },

        getLocation() {
            if (!navigator.geolocation) {
                this.gpsStatus = 'error';
                return;
            }
            this.gpsStatus = 'loading';
            navigator.geolocation.getCurrentPosition(
                (pos) => this._applyPosition(pos),
                () => {
                    // Fallback: network-based location (faster, works indoors)
                    navigator.geolocation.getCurrentPosition(
                        (pos) => this._applyPosition(pos),
                        () => { this.gpsStatus = 'error'; },
                        { enableHighAccuracy: false, timeout: 20000, maximumAge: 60000 },
                    );
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
            );
        },

        _applyPosition(pos) {
            this.latitude = pos.coords.latitude;
            this.longitude = pos.coords.longitude;
            this.address = `${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}`;
            this.gpsStatus = 'ok';
            this.checkGeofence();
        },

        retryLocation() {
            this.getLocation();
        },

        checkGeofence() {
            if (!this.needsGeofence) {
                this.geofenceOk = true;
                return;
            }
            this.geofenceOk = officeLocations.some(
                (o) => this.haversine(this.latitude, this.longitude, o.latitude, o.longitude) <= o.radius_meters,
            );
        },

        haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = ((lat2 - lat1) * Math.PI) / 180;
            const dLon = ((lon2 - lon1) * Math.PI) / 180;
            const a =
                Math.sin(dLat / 2) ** 2 +
                Math.cos((lat1 * Math.PI) / 180) * Math.cos((lat2 * Math.PI) / 180) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.asin(Math.sqrt(a));
        },

        base64urlToBuffer(base64url) {
            const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            const padded = base64.padEnd(base64.length + ((4 - (base64.length % 4)) % 4), '=');
            return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0)).buffer;
        },

        bufferToBase64url(buffer) {
            const bytes = new Uint8Array(buffer);
            let str = '';
            bytes.forEach((b) => (str += String.fromCharCode(b)));
            return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        },

        notify(message, type = 'error') {
            if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                window.Livewire.dispatch('notify', { message, type });
            }
        },

        async getWebAuthnAssertion() {
            if (!credentialId) return null;

            const currentChallenge = (await this.$wire.webauthnChallenge) || webauthnChallenge;

            const allowCredentials = [
                {
                    type: 'public-key',
                    id: this.base64urlToBuffer(credentialId),
                    transports: ['internal'],
                },
            ];

            const assertion = await navigator.credentials.get({
                publicKey: {
                    challenge: this.base64urlToBuffer(currentChallenge),
                    rpId: window.location.hostname,
                    userVerification: 'required',
                    allowCredentials,
                    timeout: 60000,
                },
            });

            return {
                credentialId: this.bufferToBase64url(assertion.rawId),
                clientDataJSON: this.bufferToBase64url(assertion.response.clientDataJSON),
            };
        },

        async doCheckIn() {
            if (this.processing) return;
            if (this.needsGeofence && !this.geofenceOk) {
                this.notify('Anda berada di luar radius kantor.', 'error');
                return;
            }

            this.processing = true;
            try {
                let assertionCredentialId = null;
                let assertionClientDataJSON = null;
                let verifyPassword = null;

                if (credentialId) {
                    this.biometricStatus = 'scanning';
                    const assertion = await this.getWebAuthnAssertion();
                    if (!assertion) {
                        this.notify(`Verifikasi ${this.biometricLabel} dibatalkan.`, 'warning');
                        this.biometricStatus = 'idle';
                        return;
                    }
                    assertionCredentialId = assertion.credentialId;
                    assertionClientDataJSON = assertion.clientDataJSON;
                    this.biometricStatus = 'idle';
                } else {
                    if (!this.password) {
                        this.notify('Masukkan kata sandi untuk verifikasi.', 'warning');
                        return;
                    }
                    verifyPassword = this.password;
                }

                await this.$wire.checkIn(
                    assertionCredentialId,
                    assertionClientDataJSON,
                    this.latitude,
                    this.longitude,
                    this.address,
                    this.workType,
                    verifyPassword,
                    this.timezone,
                );

                this.password = '';
            } catch (e) {
                if (e?.name === 'NotAllowedError') {
                    this.notify(`Verifikasi ${this.biometricLabel} dibatalkan atau gagal.`, 'warning');
                } else {
                    console.error('[checkIn]', e);
                    this.notify('Check-in gagal: ' + (e?.message || 'kesalahan jaringan'), 'error');
                }
                this.biometricStatus = 'idle';
            } finally {
                this.processing = false;
            }
        },

        async doCheckOut() {
            if (this.processing) return;

            this.processing = true;
            try {
                let assertionCredentialId = null;
                let assertionClientDataJSON = null;
                let verifyPassword = null;

                if (credentialId) {
                    this.biometricStatus = 'scanning';
                    const assertion = await this.getWebAuthnAssertion();
                    if (!assertion) {
                        this.notify(`Verifikasi ${this.biometricLabel} dibatalkan.`, 'warning');
                        this.biometricStatus = 'idle';
                        return;
                    }
                    assertionCredentialId = assertion.credentialId;
                    assertionClientDataJSON = assertion.clientDataJSON;
                    this.biometricStatus = 'idle';
                } else {
                    if (!this.password) {
                        this.notify('Masukkan kata sandi untuk verifikasi.', 'warning');
                        return;
                    }
                    verifyPassword = this.password;
                }

                await this.$wire.checkOut(
                    assertionCredentialId,
                    assertionClientDataJSON,
                    this.latitude,
                    this.longitude,
                    this.address,
                    verifyPassword,
                    this.timezone,
                );

                this.password = '';
            } catch (e) {
                if (e?.name === 'NotAllowedError') {
                    this.notify(`Verifikasi ${this.biometricLabel} dibatalkan atau gagal.`, 'warning');
                } else {
                    console.error('[checkOut]', e);
                    this.notify('Check-out gagal: ' + (e?.message || 'kesalahan jaringan'), 'error');
                }
                this.biometricStatus = 'idle';
            } finally {
                this.processing = false;
            }
        },

        onAttendanceRecorded() {},
    };
};
