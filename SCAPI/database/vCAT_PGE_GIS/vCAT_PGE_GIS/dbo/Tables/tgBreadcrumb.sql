CREATE TABLE [dbo].[tgBreadcrumb] (
    [gBreadcrumbsID]  INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [BreadcrumbUID]   VARCHAR (100)      NOT NULL,
    [ActivityUID]     NCHAR (10)         NULL,
    [ProjectID]       INT                NOT NULL,
    [SourceID]        VARCHAR (100)      NULL,
    [CreatedUserUID]  VARCHAR (100)      NOT NULL,
    [SrcDTLT]         DATETIME           NOT NULL,
    [SrvDTLTOffset]   DATETIMEOFFSET (7) CONSTRAINT [DF_g_Breadcrumbs_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [SrvDTLT]         DATETIME           CONSTRAINT [DF_g_Breadcrumbs_SrvDTLT] DEFAULT (getdate()) NULL,
    [GPSType]         VARCHAR (100)      NULL,
    [GPSSentence]     VARCHAR (400)      NULL,
    [Latitude]        FLOAT (53)         NULL,
    [Longitude]       FLOAT (53)         NULL,
    [SHAPE]           [sys].[geography]  NULL,
    [ActivityType]    VARCHAR (200)      NULL,
    [WorkQueueFilter] VARCHAR (200)      NULL,
    [BatteryLevel]    FLOAT (53)         NULL,
    [GPSTime]         DATETIME           NULL,
    [Speed]           FLOAT (53)         NULL,
    [Heading]         VARCHAR (50)       NULL,
    [GPSAccuracy]     VARCHAR (50)       NULL,
    [Satellites]      INT                NULL,
    [Altitude]        FLOAT (53)         NULL,
    CONSTRAINT [PK_g_Breadcrumbs] PRIMARY KEY CLUSTERED ([gBreadcrumbsID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'g_Activity.ActivityUID', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tgBreadcrumb', @level2type = N'COLUMN', @level2name = N'ActivityUID';

