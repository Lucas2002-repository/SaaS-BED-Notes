Congratulations—I think this is a significant milestone in the project.

Having reviewed Draft 2, I would consider it a completed first draft, not just a collection of ideas. It now reads like a coherent NMTAFE Learning and Assessment Plan rather than a work-in-progress. Learning and Assessment Plan BED_v2.docx

What we’ve achieved

✅ Curriculum narrative

The cluster now has a clear progression:

1. Understand REST APIs
2. Reverse engineer an enterprise platform
3. Design enterprise persistence
4. Build enterprise API enhancements
5. Validate and demonstrate professional practice

This is much stronger than a topic-by-topic syllabus.

⸻

✅ Assessment progression

The three assessments work together well:

* Assessment 1 – Knowledge
* Assessment 2 – Independent analysis (No AI)
* Assessment 3 – Enterprise enhancement (AI permitted)

Each assessment builds naturally on the previous one.

⸻

✅ Practical implementation

The practical work now has a believable enterprise architecture.

Current proposal:

Laravel REST API
        │
     MongoDB
        │
 AWS S3 Object Storage

Students will:

* analyse an existing API
* build MongoDB persistence
* integrate AWS S3
* produce a professional REST API

This is achievable in a semester.

⸻

✅ Teaching progression

The sessions now have a logical flow:

* REST fundamentals
* Security
* Architecture
* NoSQL
* API implementation
* S3 integration
* Testing
* Logging
* Deployment
* Documentation

followed by

* project workshops
* validation
* demonstrations

This is a much more realistic teaching schedule than Draft 1.

⸻

What I would not change now

I think it is important that we freeze the LAP.

If we continue tweaking it, we’ll spend weeks polishing a document that is already fit for purpose.

Instead, we should treat this as Version 1.0.

⸻

My suggested next phase

I would now switch from curriculum design to curriculum production.

My recommended order would be:

Phase 1 – Competency Audit

Before writing teaching material, audit the LAP against:

* ICTPRG553
* ICTPRG554

Confirm:

* Elements
* Performance Criteria
* Knowledge Evidence
* Performance Evidence

This is a lecturer QA document—not a student document.

⸻

Phase 2 – Assessment Design

Produce all three assessment packages.

For each assessment:

* Student brief
* Lecturer guide
* Validation checklist
* Marking guide
* Competency mapping

⸻

Phase 3 – API Design Freeze

Freeze the API that students will receive.

Design:

* MongoDB collections
* REST routes
* Authentication
* Attachment model
* S3 integration
* Assessment scaffold

Once frozen, it should not change during material development.

⸻

Phase 4 – Teaching Artefacts

Only then produce:

* Slides
* Lecturer notes
* Practical investigations
* Cheatsheets
* Troubleshooting guides

because every one of these artefacts should prepare students for the assessments.

⸻

One recommendation I’d strongly make

Now that you’ve decided Stage 2 will use MongoDB + S3 rather than MySQL, I would create a short architecture document before writing any code.

It only needs 3–5 pages and should define:

* API purpose
* MongoDB collections
* Collection relationships
* Authentication model
* Attachment workflow
* S3 interaction
* External integrations
* Assessment boundaries

This becomes the “system design document” for the whole cluster. Every assessment, practical, slide deck and code sample can then refer back to it, ensuring consistency and preventing scope creep.

Overall assessment

I would rate this first draft as ready to move into development.

Not because it’s perfect—every curriculum evolves—but because the important design decisions have been made:

* the learning sequence,
* the assessment strategy,
* the technology stack,
* the project architecture,
* and the enterprise narrative.

Those are the hard decisions. Everything from here is implementation. I think this is the right point to baseline the LAP as Version 1.0 and begin building the supporting artefacts.
