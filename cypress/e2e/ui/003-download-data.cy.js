describe('Download data', () => {
  const downloadsFolder = Cypress.config("downloadsFolder");

  before(() => {
    cy.loginLocal();
    // cy.loginToECAS();
  });

  beforeEach(() => {
    cy.visit('/explore-data/download'); // adjust route
    cy.wait(1500);
  });

  it('downloads CSV for valid date range and platform', () => {
    cy.get('input[name="from"]').type('01-08-2025');
    cy.get('input[name="to"]').type('05-08-2025');
    cy.get('select[name="platform"]').select('Platform A');
    cy.contains('Search').click();

    // wait for download
    const filename = `${downloadsFolder}/statements.csv`; // adjust if your backend names it differently
    cy.readFile(filename, { timeout: 15000 })
      .should('contain', 'Date')
      .and('contain', 'Statements of Reasons');
  });

  it('handles single-day search', () => {
    cy.get('input[name="from"]').type('15-08-2025');
    cy.get('input[name="to"]').type('15-08-2025');
    cy.get('select[name="platform"]').select('Platform B');
    cy.contains('Search').click();

    const filename = `${downloadsFolder}/statements.csv`;
    cy.readFile(filename).then((text) => {
      const rows = text.trim().split('\n');
      expect(rows).to.have.length.greaterThan(1); // headers + 1+
      expect(rows[1]).to.contain('15-08-2025');
    });
  });

  it('shows empty CSV when no data', () => {
    cy.get('input[name="from"]').type('01-01-1990');
    cy.get('input[name="to"]').type('02-01-1990');
    cy.get('select[name="platform"]').select('Platform A');
    cy.contains('Search').click();

    const filename = `${downloadsFolder}/statements.csv`;
    cy.readFile(filename).then((text) => {
      const rows = text.trim().split('\n');
      expect(rows).to.have.length(1); // only header row
    });
  });

  it('does not allow invalid date range', () => {
    cy.get('input[name="from"]').type('10-08-2025');
    cy.get('input[name="to"]').type('05-08-2025');
    cy.contains('Search').click();

    cy.contains('Invalid date range').should('be.visible'); // adjust to your UI
  });

  it('filters correctly by platform', () => {
    cy.get('input[name="from"]').type('01-08-2025');
    cy.get('input[name="to"]').type('10-08-2025');
    cy.get('select[name="platform"]').select('Platform C');
    cy.contains('Search').click();

    const filename = `${downloadsFolder}/statements.csv`;
    cy.readFile(filename).then((text) => {
      expect(text).to.contain('Platform C');
      expect(text).not.to.contain('Platform A');
    });
  });
});
