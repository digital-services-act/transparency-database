@php use App\Models\Statement; @endphp
@props(['statements' => null])

<style>
    .statement-row:hover {
        background-color: #EBEBEB !important;
        cursor: pointer !important;
    }
</style>

<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Platform</th>
        <th class="ecl-table__header">Restrictions</th>
        <th class="ecl-table__header">Category</th>
        <th class="ecl-table__header">Creation Date</th>


    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($statements as $statement)

        <tr class="ecl-table__row statement-row" data-url="{{ route('statement.show', [$statement]) }}">
            <td class="ecl-table__cell">{{$statement->platform_name}}</td>
            <td class="ecl-table__cell">{{$statement->restrictions()}}</td>
            <td class="ecl-table__cell">{{Statement::STATEMENT_CATEGORIES[$statement->category]}}</td>
            <td class="ecl-table__cell">{{ $statement->created_at->format('Y-m-d') }}</td>
        </tr>

    @endforeach
    </tbody>
</table>


<script>
  document.addEventListener('DOMContentLoaded', (event) => {
    let rows = document.getElementsByClassName('statement-row');
    for (let i = 0; i < rows.length; i++) {
      rows[i].addEventListener('click', (e) => {
        document.location.href = rows[i].getAttribute('data-url');
      });
    }
  });
</script>

{{ $statements->links('paginator') }}