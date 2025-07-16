<?php

namespace App\Enums;

// Open = ticket ouvert sans intervention
// Ongoing = ticket ouvert avec au moins une intervention
// Closed = ticket résolu

enum TicketStatus: string
{
    case OPEN = 'open';
    case ONGOING = 'ongoing';
    case CLOSED = 'closed';
}
