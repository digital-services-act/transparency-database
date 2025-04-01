@php use App\Models\Statement; @endphp
@php use App\Models\Platform; @endphp

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

    <tr class="ecl-table__row statement-row" data-url="/statement/{{ $statement->id }}">
      @if($statement->id < 100000000000)
      <td class="ecl-table__cell" data-ecl-table-header="Platform">{{$statement->platform_name}}</td>
    @else
      <td class="ecl-table__cell" data-ecl-table-header="Platform">{{Platform::find($statement->platform_id)->name}}
      </td>
    @endif
      <td class="ecl-table__cell" data-ecl-table-header="Restrictions">{{Statement::restrictionsString($statement)}}
      </td>
      <td class="ecl-table__cell" data-ecl-table-header="Category">
      {{Statement::STATEMENT_CATEGORIES[$statement->category]}}
      </td>
      <td class="ecl-table__cell" data-ecl-table-header="Creation Date">{{ $statement->created_at->format('Y-m-d') }}
      </td>
    </tr>

  @endforeach
  </tbody>
</table>



<script>
  document.addEventListener('DOMContentLoaded', (event) => {
    let rows = document.getElementsByClassName('statement-row')
    for (let i = 0; i < rows.length; i++) {
      rows[i].addEventListener('click', (e) => {
        document.location.href = rows[i].getAttribute('data-url')
      })
    }
  })
</script>

{{ $statements->links('paginator') }}