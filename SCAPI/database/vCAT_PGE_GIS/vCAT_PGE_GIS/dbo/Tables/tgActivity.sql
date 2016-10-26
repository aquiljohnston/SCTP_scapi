CREATE TABLE [dbo].[tgActivity] (
    [gActivityID]     INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [ActivityUID]     VARCHAR (100)      NULL,
    [ProjectID]       INT                NULL,
    [SourceID]        VARCHAR (100)      NULL,
    [CreatedUserUID]  VARCHAR (100)      NULL,
    [ModifiedUserUID] VARCHAR (100)      NULL,
    [SrvDTLT]         DATETIME           CONSTRAINT [DF_g_Activity_SrvDTLT] DEFAULT (getdate()) NULL,
    [SrvDTLTOffset]   DATETIMEOFFSET (7) CONSTRAINT [DF_g_Activity_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [SrcDTLT]         DATETIME           NULL,
    [GPSType]         VARCHAR (100)      NULL,
    [GPSSentence]     VARCHAR (400)      NULL,
    [Latitude]        FLOAT (53)         NULL,
    [Longitude]       FLOAT (53)         NULL,
    [SHAPE]           [sys].[geography]  NULL,
    [Comments]        VARCHAR (500)      NULL,
    [ActivityType]    VARCHAR (200)      NULL,
    [BatteryLevel]    FLOAT (53)         NULL,
    [StartDTLT]       DATETIME           NULL,
    [StopDTLT]        DATETIME           NULL,
    [ElapsedSec]      INT                NULL,
    CONSTRAINT [PK_t_Activity] PRIMARY KEY CLUSTERED ([gActivityID] ASC)
);

