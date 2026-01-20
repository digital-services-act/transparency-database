import { generateStatementRequestBody } from "../../support/e2e";

const _ = Cypress._;
const url = `${Cypress.env("apiUrl")}/statements`;
const token = Cypress.env("token");
const headers = {
  "Content-Type": "application/json",
  Accept: "application/json",
};

context("Multiple statements endpoint", () => {
  it("responds with 422 for empty request body", () => {
    cy.request({
      method: "POST",
      url: url,
      headers: { ...headers, Authorization: `Bearer ${token}` },
      failOnStatusCode: false,
      body: {},
    }).then((response) => {
      expect(response.status).to.eq(422);
    });
  });

  it("creates multiple statements with correct data and returns 201 CREATED", () => {
    cy.fixture("statements").then((data) => {
      const count = 100;
      const statements = _.range(count).map(() =>
        generateStatementRequestBody(data),
      );

      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        failOnStatusCode: false,
        body: { statements: statements },
      }).then((response) => {
        expect(response.status).to.eq(201);

        const puids = response.body.statements.map((s) => s.puid);

        cy.wait(2000);

        // Let's check Opensearch for the puids
        cy.request({
          method: "POST",
          url: `${Cypress.env("apiUrl")}/opensearch/sql`,
          headers: { ...headers, Authorization: `Bearer ${token}` },
          body: {
            query: `SELECT puid from statement_index order by id desc limit ${count}`,
          },
        }).then((searchResponse) => {
          expect(searchResponse.status).to.eq(200);
          const rows = searchResponse.body.datarows.flat();

          puids.forEach((puid) => {
            expect(rows).to.contain(puid);
          });
        });
      });
    });
  });

  it("responds 422 if max number of statements exceeded", () => {
    cy.fixture("statements").then((data) => {
      const statements = _.range(101).map(() =>
        generateStatementRequestBody(data),
      );

      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        failOnStatusCode: false,
        body: { statements: statements },
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.message).to.eq(
          "The statements field must have between 1 and 100 items.",
        );
      });
    });
  });

  it("responds 422 if one of the statements has a validation issue", () => {
    cy.fixture("statements").then((data) => {
      const statements = _.range(10).map(() =>
        generateStatementRequestBody(data),
      );

      statements[0].category = "INVALID_CATEGORY";

      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        failOnStatusCode: false,
        body: { statements: statements },
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.errors.statement_0.category).to.contain(
          "The selected category is invalid.",
        );
      });
    });
  });

  it("responds 422 for duplicate puid in request", () => {
    cy.fixture("statements").then((data) => {
      const statements = _.range(20).map(() =>
        generateStatementRequestBody(data),
      );

      statements[1].puid = statements[0].puid;
      statements[5].puid = statements[0].puid;
      statements[10].puid = statements[9].puid;

      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        failOnStatusCode: false,
        body: { statements: statements },
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.message).to.eq(
          "The platform identifier(s) are not all unique within this call.",
        );
      });
    });
  });

  let puid = "";

  it("responds 422 for duplicate request", () => {
    cy.fixture("statements").then((data) => {
      const statements = _.range(10).map(() =>
        generateStatementRequestBody(data),
      );

      // First request - create the statement
      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        body: { statements: statements },
      }).then((response) => {
        expect(response.status).to.eq(201);

        // Store this id to use in the next test
        puid = response.body.statements[0].puid;

        // Second request - try to create with same body
        cy.request({
          method: "POST",
          url: url,
          headers: { ...headers, Authorization: `Bearer ${token}` },
          failOnStatusCode: false,
          body: { statements: statements },
        }).then((response) => {
          expect(response.status).to.eq(422);
          expect(response.body.message).to.contain(
            "The platform identifier(s) are not all unique within this call",
          );
        });
      });
    });
  });

  it("responds 422 for duplicate request", () => {
    cy.fixture("statements").then((data) => {
      const statements = _.range(10).map(() =>
        generateStatementRequestBody(data),
      );
      statements[0].puid = puid; // Use the puid from the previous test

      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        body: { statements: statements },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.message).to.contain(
          "The platform identifier(s) are not all unique within this call",
        );
      });
    });
  });

  it("responds 422 for mix of duplicate puids (some in request, some in DB)", () => {
    cy.fixture("statements").then((data) => {
      const existing = generateStatementRequestBody(data);

      // Create one statement first
      cy.request({
        method: "POST",
        url: url,
        headers: { ...headers, Authorization: `Bearer ${token}` },
        body: { statements: [existing] },
      }).then(() => {
        const statements = _.range(10).map(() =>
          generateStatementRequestBody(data),
        );

        // Make some duplicates within request
        statements[1].puid = statements[0].puid;

        // Make one duplicate with existing DB record
        statements[5].puid = existing.puid;

        cy.request({
          method: "POST",
          url: url,
          headers: { ...headers, Authorization: `Bearer ${token}` },
          failOnStatusCode: false,
          body: { statements: statements },
        }).then((response) => {
          expect(response.status).to.eq(422);
          expect(response.body.message).to.contain(
            "The platform identifier(s) are not all unique within this call",
          );
        });
      });
    });
  });
});
