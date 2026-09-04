# Lamii UI/UX Design Plan

## 1. Purpose

This document is the UI/UX source of truth for the next stage of Lamii product development.

Lamii should feel like a polished, modern, mobile-first social discovery product rather than a conventional Laravel website. The product should be fluid, visually confident, fast to understand, and pleasant to use with one hand.

The interaction quality should be comparable to leading mobile discovery/social applications: natural gestures, responsive cards, smooth transitions, clear feedback, and minimal friction. Lamii must remain its own product and must not copy another product's branding, artwork, wording, or exact interface.

## 2. Visual Source of Truth

The selected design reference is the **second Lamii discovery/profile concept supplied by the product owner**.

The reference establishes these principles:

- Deep, dark backgrounds rather than light/pastel application surfaces.
- Strong teal as a primary brand color, with a much darker teal/near-black teal underneath it.
- High-contrast accent colors used deliberately: coral/red, orange, purple, and other saturated accents can coexist with the teal system.
- Strong gradients and color blending instead of weak, washed-out versions of colors.
- Large expressive photography.
- Rounded cards with depth and subtle borders/glows.
- Bright, confident action buttons.
- Mobile-first layouts with large touch targets.
- A premium, energetic, human feeling.
- Visual hierarchy should be strong without making the interface feel crowded.

### Color philosophy

Do **not** default to pale teal, washed-out cyan, or low-contrast pastel UI.

When teal is used, prefer a strong teal blended into a much darker teal/blue-black foundation. Accent colors should have enough saturation and contrast to remain visible against the dark surface.

The final implementation should centralize colors as design tokens/CSS variables so that the visual theme can be adjusted without rewriting components.

Suggested conceptual token families (exact values should be tuned during implementation):

- `--lamii-bg-deep`: near-black blue/teal foundation.
- `--lamii-bg-surface`: very dark teal surface.
- `--lamii-teal-strong`: primary saturated teal.
- `--lamii-teal-deep`: dark teal companion tone.
- `--lamii-coral`: positive/like accent.
- `--lamii-orange`: energetic secondary accent.
- `--lamii-purple`: expressive tertiary accent.
- `--lamii-text`: high-contrast primary text.
- `--lamii-text-muted`: readable secondary text.
- `--lamii-border`: subtle teal-tinted border.

These are design concepts, not a command to use these exact names or values. The implementation should establish a coherent token system.

## 3. Product Experience Goal

The core experience is:

**Discover → Inspect → Wave/Like → Accept → Connect → Chat**

Discovery should feel like browsing a living local community, not filling out a directory.

The user should immediately understand:

1. How many people are around them.
2. What radius is being used.
3. Who is currently visible.
4. That each person can be inspected without losing their place.
5. How to express interest.
6. How to continue into a connection or conversation.

## 4. Discovery Page

### 4.1 Primary layout

The Discovery page is the main product surface.

At the top:

- Lamii branding.
- Menu/navigation affordance.
- Notifications indicator.
- Heading such as **People around you**.
- A prominent nearby count, e.g. **243 people near you**.
- Current discovery radius, e.g. **within 15 km**.
- A radius/filter control.

The nearby count is important. It should be visually prominent enough that a user immediately understands that Lamii is showing a real local population rather than a fixed list.

### 4.2 Medium scrollable profile cards

Do **not** make the main Discovery page a full-screen Tinder-style card stack.

The preferred pattern is a horizontally or naturally scrollable collection of **medium-sized profile cards**.

Each card should provide enough information for a quick decision without attempting to show the entire profile.

A card should typically contain:

- Large profile image.
- Online/presence indicator when available.
- Name and age where product rules permit.
- Verification indicator where applicable.
- Short role/profession.
- City/locality.
- Distance.
- A small action affordance such as Wave/Like.

The card should be visually rich but not overloaded with text.

### 4.3 Card scrolling behavior

Cards should support smooth horizontal scrolling on mobile.

Requirements:

- Use native-feeling touch scrolling.
- Preserve momentum.
- Avoid abrupt snapping unless snapping clearly improves usability.
- Keep neighboring cards partially visible when appropriate so the user understands that more people are available.
- Use lazy loading for profile images.
- Avoid layout shifts while images load.
- Do not make the entire page feel like a carousel if there are many people; the interaction should remain understandable and performant.

### 4.4 Tapping a card

Tapping a medium card opens the **full profile experience**.

The transition should feel continuous rather than like navigating to an unrelated page.

Preferred behavior:

- Card expands/transitions into the profile detail view.
- Preserve the selected person's identity during the transition.
- Provide a clear back action.
- Avoid forcing the user to restart Discovery after returning.

## 5. Full Profile Experience

The full profile is the immersive inspection surface shown after tapping a discovery card.

### Header/photo area

- Large profile image.
- Back button.
- Optional overflow/menu action.
- Name, age, verification state.
- Profession/role.
- Location and distance.
- Presence/online state when available.

### Profile content

Use visually separated sections rather than one large text block:

- **About me**
- **Interests**
- **I'm looking for** / connection intent
- Other approved profile attributes as the product evolves.

Interests should use colorful chips/pills. Different accent colors may be used, but the palette must remain coherent with the dark teal foundation.

### Profile actions

The primary action area should be easy to reach with one hand.

Conceptually:

- Pass / dismiss.
- Wave.
- Like.

The exact product rules for Wave vs Like should remain consistent with the backend semantics. Do not introduce a UI action that has no defined API behavior.

The Wave action should be visually distinctive because Wave is part of Lamii's core identity.

## 6. Motion and Interaction Design

Fluidity is a first-class requirement.

### Principles

- Motion should communicate cause and effect.
- Animations should be short and purposeful.
- Avoid animation for decoration alone.
- Respect `prefers-reduced-motion`.
- Avoid blocking the user while animations play.

### Card interactions

For future gesture enhancements, cards may support a natural drag interaction:

- Card follows the user's finger.
- Small rotation/translation can communicate direction.
- Action state becomes clearer as the gesture passes a threshold.
- Release below the threshold returns the card smoothly to its original position.
- Release beyond the threshold completes the action and transitions to the next state.

This should be implemented only where it improves the medium-card/full-profile model. Do not turn the entire Discovery page into a clone of a swipe-only dating interface.

### Micro-interactions

Use subtle motion for:

- Wave confirmation.
- Like confirmation.
- Notification arrival.
- Chat message appearance.
- Online status changes.
- Loading/skeleton states.
- Navigation transitions.
- Modal/sheet opening and closing.

Animations should degrade gracefully on lower-powered devices.

## 7. Discovery Information Architecture

The page should answer these questions in order:

1. **Where am I?** — People around you.
2. **How many are there?** — Nearby count.
3. **How far are they?** — Radius and per-person distance.
4. **Who is here?** — Medium profile cards.
5. **Who interests me?** — Tap for full profile.
6. **What can I do?** — Wave/Like/Pass.

Avoid burying the nearby count or radius beneath decorative UI.

## 8. Empty, Loading, and Error States

Every Discovery state must be intentionally designed.

### Loading

- Use skeleton cards or a visually consistent loading treatment.
- Avoid a blank screen.
- Preserve the dark teal visual system.
- Do not flash unrelated content while loading.

### No people nearby

Explain the state clearly:

- No matching people currently visible.
- Suggest increasing the radius if appropriate.
- Provide a clear retry/refresh action.

### Location unavailable

Explain why location is useful and give the user a clear path to retry permission or update location.

Do not expose raw latitude/longitude in the UI.

### Network/API failure

Use concise messaging and a retry action. Preserve already-loaded cards where possible rather than resetting the entire experience.

## 9. Navigation

Mobile navigation should be optimized for the core product loop.

The visual reference uses a bottom navigation model with:

- Discover
- Waves
- Chats
- Profile

This is the preferred direction for mobile/PWA.

The active tab should be clearly visible using the strong Lamii accent color. Unread badges should be small but unmistakable.

Desktop navigation may adapt into a sidebar/top navigation, but the mobile information architecture should remain the priority.

## 10. Notifications

Notifications should support the social loop without becoming noisy.

Examples:

- New Wave.
- Connection accepted.
- New message.

Unread states should be visually obvious but restrained.

Tapping a notification should take the user to the relevant context when possible.

## 11. Chat UX

Chat should feel instantaneous and modern.

Requirements:

- Clear conversation header.
- Strong distinction between sent and received messages.
- Comfortable message spacing.
- Good keyboard handling on mobile.
- Scroll position preservation.
- New-message feedback.
- Unread state handling.
- Optimistic UI only where it can be reconciled safely with server state.

The final chat experience should use Laravel broadcasting/Reverb for real-time updates when the real-time infrastructure is implemented.

## 12. PWA Requirements

The UI should be designed so the same product can become a high-quality installable PWA.

Requirements include:

- Responsive mobile-first layout.
- Web app manifest.
- Appropriate application icons.
- Service worker/app-shell strategy.
- Installable experience.
- Safe-area support for modern phones.
- Touch-friendly controls.
- Keyboard-aware layouts.
- Offline/loading states that fail gracefully.
- Push notification strategy where supported.
- Location permission UX.
- No dependence on hover-only interactions.

The PWA should feel like an app when installed, not simply like a web page inside a browser.

## 13. Native Mobile Preparation

Do not build native iOS/Android screens before the web/PWA UX and API behavior are stable.

The Laravel API should remain the shared product contract.

The native clients should eventually reuse the same concepts:

- authentication
- discovery
- waves
- connections
- conversations
- messages
- notifications
- profile
- location/discovery preferences

UI components should not be designed around assumptions that only work on desktop browsers.

## 14. Accessibility

The visual style is bold, but accessibility is mandatory.

Requirements:

- Sufficient text/background contrast.
- Visible focus states.
- Touch targets large enough for comfortable mobile use.
- Semantic buttons/links.
- Descriptive accessible labels for icon-only controls.
- Keyboard navigation where applicable.
- Reduced-motion support.
- Do not communicate important states by color alone.
- Loading/error states must be understandable to assistive technologies.

Strong colors must not come at the expense of readability.

## 15. Responsive Strategy

Design mobile-first, then enhance for larger screens.

### Small phones

Prioritize:

- one-handed use
- compact header
- medium discovery cards
- bottom navigation
- safe-area spacing
- readable type
- large actions

### Tablets

Use additional horizontal space for card collections and profile layouts without simply scaling everything up.

### Desktop

Desktop can use:

- wider discovery card collections
- richer profile panels
- side navigation or wider navigation treatment
- split-view chat where appropriate

However, desktop should still feel like the same Lamii product.

## 16. Component Strategy

Before implementing the redesign, identify reusable UI primitives rather than duplicating markup across pages.

Likely primitives include:

- `LamiiHeader`
- `BottomNavigation`
- `NearbyCount`
- `RadiusSelector`
- `ProfileCard`
- `ProfileCardCollection`
- `ProfileDetail`
- `InterestChip`
- `PresenceIndicator`
- `VerificationBadge`
- `ActionButton`
- `WaveButton`
- `LikeButton`
- `NotificationBadge`
- `EmptyState`
- `LoadingCard`
- `Toast/Feedback`
- `ChatMessage`

Exact component technology should follow the existing project architecture. Do not introduce a large frontend framework solely for visual polish unless there is a clear technical reason.

## 17. Existing Lamii Backend Contract

The UI must respect existing backend behavior.

The Discovery UI currently expects a response conceptually containing:

- `people`
- `radius_km`
- per-person `connection_state`
- `connection_id`
- `avatar`
- `name`
- `distance`
- `bio`

If the backend response is improved, update the UI and tests together rather than silently changing the contract.

The UI must never display sensitive backend fields simply because they happen to be present in an API response.

## 18. Performance Requirements

The visual redesign must not sacrifice performance.

Priorities:

- Lazy-load profile images.
- Use appropriately sized images.
- Avoid unnecessary JavaScript on initial load.
- Avoid layout shifts.
- Avoid expensive continuous animations.
- Keep scroll performance smooth.
- Debounce/throttle location refresh and expensive UI operations.
- Preserve cached/previously loaded data where appropriate.

A beautiful Discovery screen that stutters on a mid-range phone is not acceptable.

## 19. Design Anti-Patterns to Avoid

Do not:

- Turn Discovery into a giant single-card Tinder clone.
- Use pale teal everywhere.
- Use many unrelated bright colors without a token system.
- Put too much text on discovery cards.
- Hide the nearby count.
- Make profile details difficult to reach.
- Use tiny touch targets.
- Depend on hover interactions.
- Create excessive glassmorphism that reduces readability.
- Add animations that delay actions.
- Rewrite the entire existing UI without preserving working product behavior.
- Introduce UI actions that the API does not support.
- Leak coordinates, private profile fields, or internal IDs unnecessarily.

## 20. Implementation Order

The recommended implementation sequence is:

### Step 1 — Audit current UI

Inspect the existing Blade views, routes, controllers, JavaScript, and CSS before changing them.

Identify what can be reused and what needs restructuring.

### Step 2 — Establish design tokens

Create the dark teal foundation, strong teal primary color, saturated accent palette, typography scale, spacing scale, radii, shadows, borders, and motion tokens.

### Step 3 — Rebuild Discovery presentation

Implement:

- nearby count
- radius display/control
- medium scrollable cards
- card loading/empty/error states
- responsive behavior
- safe image loading

### Step 4 — Implement full profile interaction

Add the tap-to-profile experience with the visual language from the selected reference.

### Step 5 — Refine actions

Polish Wave/Like/Pass feedback, connection states, and error handling.

### Step 6 — Navigation and notifications

Refine bottom navigation, unread badges, notification presentation, and transitions.

### Step 7 — Chat UX

Polish conversations and message states in preparation for Reverb/WebSocket real-time behavior.

### Step 8 — Motion/performance pass

Tune transitions, touch interactions, loading states, image performance, and reduced-motion behavior.

### Step 9 — PWA pass

Add installability, manifest, service worker strategy, safe-area handling, push notification foundations, and mobile-specific behavior.

### Step 10 — Reverb/real-time integration

Connect the polished UI to Laravel broadcasting/Reverb for real-time message and relevant social-event updates.

### Step 11 — Device testing

Test on:

- small Android phones
- larger Android phones
- iPhones with modern safe areas
- tablet widths
- desktop browsers
- slow network conditions
- reduced-motion settings

## 21. AI Agent Instructions

An AI coding/design agent reviewing Lamii should treat this document as the product owner's current UI/UX direction.

Before changing UI code:

1. Inspect the current implementation.
2. Preserve working API contracts and product behavior.
3. Identify reusable existing components/styles.
4. Compare the current UI against this plan.
5. Make incremental, reviewable changes.
6. Run relevant tests after changes.
7. Do not replace working architecture merely to achieve visual similarity.

When there is a conflict between an old UI implementation and this document, this document represents the newer intended visual direction, while existing backend behavior remains the source of truth for what actions are actually supported.

The agent should prioritize the following qualities, in order:

1. Usability and clarity.
2. Mobile responsiveness.
3. Fluid interaction.
4. Performance.
5. Accessibility.
6. Visual polish.

## 22. Definition of Done for Discovery UX

The Discovery redesign is considered successful when a user can:

- immediately see how many people are nearby;
- understand the current discovery radius;
- smoothly scroll through medium-sized profile cards;
- understand enough from a card to decide whether to inspect someone;
- tap a card and enter a polished full profile experience;
- return to Discovery without losing context;
- Wave/Like/Pass without confusion;
- clearly see success/failure feedback;
- use the entire flow comfortably on a phone with one hand;
- understand unread Waves/Chats/Notifications;
- use the interface with reduced motion enabled;
- experience smooth scrolling and image loading on a mid-range mobile device.

## 23. Product Principle

**Lamii should feel alive.**

The interface should communicate that real people are nearby, available for discovery, and able to become meaningful connections.

Strong colors, deep gradients, expressive photography, fluid motion, and clear information hierarchy should reinforce that feeling without overwhelming the user.

The goal is not to make Lamii look like another app. The goal is to make Lamii feel as polished and fluid as the best mobile social products while remaining unmistakably Lamii.
