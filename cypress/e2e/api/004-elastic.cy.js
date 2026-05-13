import { faker } from "@faker-js/faker";
import { generateStatementRequestBody } from "../../support/e2e";

const _ = Cypress._;
const baseUrl = `${Cypress.env("apiUrl")}/elastic`;
const token = Cypress.env("token");
const headers = {
  "Content-Type": "application/json",
  Accept: "application/json",
};
const index = "statement_index";
const contextIfElasticEnabled = Cypress.env("elasticSearchEnabled")
  ? context
  : context.skip;

// search
// count
// explain
// cacheclear
// aggregates
// platforms
// labels
// totalw
// datetotal
// platformdatetotal
// datetotalrange
// datetotalsrange

const doRequest = (url, body = {}) => {
  return cy.request({
    method: "POST",
    url: url,
    headers: { ...headers, Authorization: `Bearer ${token}` },
    failOnStatusCode: false,
    body: body,
  });
};

contextIfElasticEnabled("ElasticSearch endpoint", () => {
  // /sql endpoint
  let url = `${baseUrl}/sql`;
  it("responds with 422 for empty request body", () => {
    doRequest(url, {}).then((response) => {
      expect(response.status).to.eq(422);
    });
  });

  it("responds with 200 for proper index name", () => {
    const query = `SELECT 1 FROM ${index}  limit 1`;

    doRequest(url, { query: query }).then((response) => {
      expect(response.status).to.eq(200);
    });
  });

  it("gets the count for the entire index", () => {
    const query = `SELECT COUNT(id) FROM ${index}`;

    doRequest(url, { query: query }).then((response) => {
      expect(response.status).to.eq(200);
      expect(response.body).to.have.property("total");
    });
  });

  it("checks there are no records on the date exactly 6 months ago", () => {
    let sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
    sixMonthsAgo.setDate(sixMonthsAgo.getDate() - 1);
    sixMonthsAgo = sixMonthsAgo.toISOString().slice(0, 10);

    const query = `SELECT COUNT(id) FROM ${index} WHERE created_at between '${sixMonthsAgo}T00:00:00' AND '${sixMonthsAgo}T23:59:59'`;
    doRequest(url, { query: query }).then((response) => {
      expect(response.status).to.eq(200);
      expect(response.body.datarows[0][0]).to.eq(0);
    });
  });

  it("checks there are no records older than 6 months, total", () => {
    let sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
    sixMonthsAgo = sixMonthsAgo.toISOString().slice(0, 10);

    const query = `SELECT COUNT(id) FROM ${index} WHERE created_at < '${sixMonthsAgo}T00:00:00'`;
    doRequest(url, { query: query }).then((response) => {
      expect(response.status).to.eq(200);
      expect(response.body.datarows[0][0]).to.eq(0);
    });
  });
});
