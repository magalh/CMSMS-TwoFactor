# Amazon Q Context Rules – TwoFactor (CMS Made Simple)

## Module Purpose

TwoFactor is a security-focused authentication module for CMS Made Simple.

Its purpose is to:
- Provide secure two-factor authentication (2FA) for CMS users.
- Protect CMS installations from brute-force and credential attacks.
- Offer both free and premium security capabilities.
- Optionally integrate with a remote Managed SMS Credit System for OTP delivery.

TwoFactor is authentication infrastructure. Security always takes priority over convenience.

---

## Product Structure

### Free Version Includes:
- TOTP (Authenticator apps)
- Email verification
- SMS via user's own Twilio credentials AND Managed SMS integration
- Backup codes
- Basic 2FA functionality

### Premium Version Includes:
- Enforced 2FA for all users
- Rate limiting and brute-force protection
- Trusted devices
- Email security alerts
- Admin management dashboard
- Granular permissions
- Priority support
- SMS via user's own Twilio credentials AND Managed SMS integration

Amazon Q must preserve strict separation between Free and Premium logic.

---

## Managed SMS Credit System (Remote API)

When using Managed SMS:

- All credit calculations are handled remotely.
- All country-to-credit conversions are handled remotely.
- All verification cost logic is handled remotely.
- All credit deductions are handled remotely.
- All logging of SMS usage is handled remotely.

TwoFactor must NOT:
- Calculate credits locally.
- Store or modify credit balances.
- Perform country-based credit conversions.
- Trust frontend values related to credits.

TwoFactor must ONLY:
- Validate license status.
- Normalize and validate phone numbers (E.164).
- Call the secure API Gateway endpoint.
- Handle API responses safely.
- Handle failures gracefully.

TwoFactor acts as a secure client — not the billing engine.

---

## Hack & Abuse Prevention Model

TwoFactor operates in a hostile environment.

It must:

- Never trust client-side input.
- Sanitize and validate all user input.
- Normalize phone numbers before sending to API.
- Enforce local rate limiting.
- Implement exponential backoff after failed OTP attempts.
- Lock accounts after repeated failures.
- Prevent OTP spamming.
- Never expose API credentials.
- Never log full phone numbers (store masked or hashed versions only).

Security layers must exist both locally and remotely.

---

## Remote API Communication Rules

- Always validate API responses.
- Handle network failures gracefully.
- Never assume verification success without explicit confirmation.
- Never mark OTP as valid without remote verification response.
- Implement timeouts and safe error handling.
- Fail securely (deny access if verification state is uncertain).

---

## Development Philosophy

When generating code:

- Prefer defensive coding over simplicity.
- Keep authentication logic separate from remote SMS logic.
- Do not mix billing logic into this module.
- Avoid assumptions about credit state.
- Always assume the remote API is authoritative.

This module is authentication infrastructure and must be written accordingly.
