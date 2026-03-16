# Product Requirements Document: Berghs Festival 2026 Website

## 1. Overview

Berghs School of Communication hosts an annual festival where students showcase their work. This document defines the requirements for the festival website. The site serves two purposes: promoting the event before it happens, and presenting student projects once they go live.

The site will launch in two phases. Phase 1 goes live before invitations are sent out and includes everything except student projects. Content that is not yet finalized at Phase 1 launch will be represented by placeholder sections. Phase 2 adds student projects, which will be published approximately the day before the festival.

## 2. Technical Requirements

### Hosting and Domain

The site will be hosted on its own domain, not as a subdomain of berghs.se. The domain name is TBD. No constraints on TLD (.se, .com, etc.) as long as it is a conventional choice.

### CMS

WordPress, using a custom classic theme. Content editing will happen through the WordPress admin interface. Advanced Custom Fields (ACF) will be used for structured content entry on custom post types.

### Content Editing Roles

Multiple team members (including visual designers) will edit content through WP admin; roles and permissions should be configured accordingly.

### Version Control

We will set up and own the Git repository.

### Language

All content on the site will be in English only.

### Performance

No specific performance targets from the client. We own the responsibility of optimizing image and video assets for acceptable load times.

### Responsive Design

The site must be fully responsive. Mobile traffic is expected to dominate, so mobile should be treated as the primary viewport during design and development.

## 3. Legal and Accessibility

### GDPR

No user tracking on the site. No data collection concerns.

### Accessibility

The site must comply with applicable Swedish accessibility legislation (based on WCAG). Image alt texts are encouraged but not strictly required. We can ask students to provide alt text with their submissions. All videos must be captioned, but captioning is not our responsibility.

## 4. Content and Submissions

### Content Ownership

| Content                                                | Owner                     |
| ------------------------------------------------------ | ------------------------- |
| Student project texts and images                       | Students                  |
| Submission spec / guidelines                           | Us                        |
| "About Berghs" copy                                    | Berghs                    |
| IQ (alcohol policy) text                               | Us (adapted to our theme) |
| Biletto ticketing page content (image + copy)          | Us                        |
| berghs.se/aktuellt/berghs-festival-2026 (text + image) | Us                        |

### Student Submission Format

Each project submission is structured with predefined fields:

- Project image (3:2 aspect ratio recommended for mobile and desktop; students should be told images may be cropped)
- Text fields with character limits under fixed headings (e.g. Company, Background, Solution)
- Case film (hosting approach TBD; likely external embed via YouTube or Vimeo)
- Group members with names and program/class
- Both group and individual projects must be supported

### Student Submission Workflow

Students submit project content through an external form (not built into the site). Submissions are then entered manually into WordPress. The volume of submissions arriving the day before the festival is a known bottleneck that should be planned for when staffing data entry.

### Content Deadline

Students are expected to submit the day before the festival. Late submissions are common and should be planned for.

## 5. Site Structure and Features

### 5.1 Student Projects

The core content of the site. Published approximately the day before the festival. Should include everything that has appeared on previous festival sites.

### 5.2 Event Schedule

A program or schedule for the festival day covering food, performances, and other activities.

### 5.3 Installations

There will be physical installations at the festival. We are responsible for this section. The specific installations are not yet decided, so the section needs to accommodate content added later.

### 5.4 Map / Directions

Map or directions to the venue.

### 5.5 IQ Text (Alcohol Policy)

A text about alcohol and its role at the festival, required by IQ (the Swedish organization promoting responsible drinking). This appears on the site and can be adapted to fit our visual theme.

### 5.6 Sponsors

Displayed in the footer. Placement and design are up to us.

### 5.7 Signup / Tickets

We do not build ticketing functionality. The site links to an external platform (likely Biletto). We deliver the image and copy for that external page. A reciprocal link from the ticketing page back to our site should also be set up.

### 5.8 About Berghs

A section about the school. Copy is provided by Berghs, not written by us. The student agency ("Studentbyrån") may be folded into this section.

### 5.9 Global Elements

**Header**

- Berghs logo, clickable, linking to berghs.se

**Footer**

- Sponsor logos
- Address, phone number
- Links to social media

**Call to Action**
A primary CTA should be present (likely linking to ticket signup). Exact copy and placement TBD.

## 6. External Deliverables

Beyond the website itself, we are responsible for delivering assets to two external pages:

1. **Biletto ticketing page:** Image and copy for the event listing.
2. **berghs.se/aktuellt/berghs-festival-2026:** Text and image for the news/event post on Berghs' main site.

## 7. Timeline

| Milestone                             | Target                                                  |
| ------------------------------------- | ------------------------------------------------------- |
| Phase 1: Site live (without projects) | Before invitations go out. Exact date TBD with Camilla. |
| Phase 2: Student projects published   | Approximately the day before the festival.              |

**Open question:** When can we expect to receive all baseline content we need (About Berghs copy, sponsor logos, schedule details, etc.)?

## 8. Guiding Principles

- Start simple. The MVP should be lean and functional before we add polish.
- If time allows, we may produce a case film documenting the project. This would serve as marketing material for both Berghs and our team.
