import * as React from "react"

import { cn } from "@/lib/utils"

function Table({ className, ...props }: React.ComponentProps<"table">) {
  return (
    <div className="w-full space-y-4 overflow-y-auto">
    <table
      data-slot="table"
      className={cn(
        "w-full min-w-max table-auto overflow-y-auto text-left text-sm leading-normal",
        className
      )}
      {...props}
      />
      </div>
  )
}

function TableCaption({ className, ...props }: React.ComponentProps<"caption">) {
  return (
    <caption
      data-slot="table-caption"
      className={cn("mb-2 text-center font-semibold", className)}
      {...props}
    />
  )
}

function TableHead({ className, ...props }: React.ComponentProps<"thead">) {
  return (
    <thead
      data-slot="table-head"
      className={cn("border-blue-gray-100 bg-sidebar border-b", className)}
      {...props}
    />
  )
}

function TableHeadRow({ className, ...props }: React.ComponentProps<"tr">) {
  return (
    <tr
      data-slot="table-head-row"
      className={cn("", className)}
      {...props}
    />
  )
}

function TableHeadData({ className, children,  ...props }: React.ComponentProps<"th">) {
  return (
    <th
      data-slot="table-head-data"
      className={cn("p-4 capitalize", className)}
      {...props}
    >
      <p className="">{children}</p>
      </th>
  )
}
function TableBody({ className, ...props }: React.ComponentProps<"tbody">) {
  return (
    <tbody
      data-slot="table-body"
      className={cn("", className)}
      {...props}
    />
  )
}


function TableBodyRow({ className, ...props }: React.ComponentProps<"tr">) {
  return (
    <tr
      data-slot="table-body-row"
      className={cn("border-b even:bg-sidebar", className)}
      {...props}
    />
  )
}

function TableBodyData({ className, children, ...props }: React.ComponentProps<"td">) {
  return (
    <td
      data-slot="table-body-row"
      className={cn("p-2", className)}
      {...props}
    >
      {children}
      </td>
  )
}


export { Table, TableBody, TableBodyData, TableBodyRow, TableCaption, TableHead, TableHeadData, TableHeadRow }
