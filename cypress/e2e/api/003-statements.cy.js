// error for one wrong out of multiple
// error for empty?
// error for ....
import { generateStatementRequestBody } from "../../support/e2e";

const _ = Cypress._;
const url = `${Cypress.env("apiUrl")}/statements`;
const token = Cypress.env("token");
const headers = {
  "Content-Type": "application/json",
  "Accept": "application/json",
};

context('Multiple statements endpoint', () => {
  it('responds with 422 for empty request body', () => {
    cy.request({
      method: "POST",
      url: url,
      headers: {...headers, "Authorization": `Bearer ${token}`},
      failOnStatusCode: false,
      body: {}
    }).then((response) => {
      expect(response.status).to.eq(422);
    });
  });

  it('creates multiple statements with correct data and returns 201 CREATED', () => {
    cy.fixture('statements').then((data) => {
      const statements = _.range(100).map(() => generateStatementRequestBody(data));

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: {statements: statements}
      }).then((response) => {
        expect(response.status).to.eq(201);
      });
    });
  });

  it("responds 422 if max number of statements exceeded", () => {
    cy.fixture('statements').then((data) => {
      const statements = _.range(101).map(() => generateStatementRequestBody(data));

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: {statements: statements}
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.message).to.eq("The statements field must have between 1 and 100 items.");
      });
    });
  });

  it("responds 422 if one of the statements has a validation issue", () => {
    cy.fixture('statements').then((data) => {
      const statements = _.range(10).map(() => generateStatementRequestBody(data));

      statements[0].category = "INVALID_CATEGORY";

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: {statements: statements}
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.errors.statement_0.category).to.contain("The selected category is invalid.");
      });
    });
  });
});
